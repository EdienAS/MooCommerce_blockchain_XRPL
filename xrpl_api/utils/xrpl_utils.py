import xrpl

from fastapi.requests import Request
from fastapi import FastAPI, HTTPException

from xrpl.asyncio.clients import AsyncJsonRpcClient
from xrpl.asyncio.transaction import safe_sign_and_autofill_transaction, send_reliable_submission
from xrpl.models.transactions.nftoken_mint import NFTokenMint, NFTokenMintFlag
from xrpl.clients import JsonRpcClient
from xrpl.wallet import Wallet

from xrpl.models.transactions.nftoken_create_offer import NFTokenCreateOffer, NFTokenCreateOfferFlag
from xrpl.models.transactions.nftoken_mint import NFTokenMint, NFTokenMintFlag
from xrpl.models.transactions.nftoken_accept_offer import NFTokenAcceptOffer
from xrpl.models.transactions.nftoken_cancel_offer import NFTokenCancelOffer
from xrpl.models.transactions.account_set import AccountSet, AccountSetFlag
from xrpl.models.transactions.nftoken_burn import NFTokenBurn
from xrpl.wallet import generate_faucet_wallet
from xrpl.models.requests import AccountNFTs
from xrpl.clients import JsonRpcClient
from xrpl.models import NFTSellOffers

from xrpl.models.requests.account_nfts import AccountNFTs
from xrpl.models.transactions.nftoken_burn import NFTokenBurn
from xrpl.models import NFTBuyOffers

from utils.helper import encrypt_sensitive_data, uri_hex, memo_json_to_hex,decrypt_sensitive_data,hex_to_ascii
import utils.mongo as MongoHelper

import logging
import requests
import json

logger = logging.getLogger(__name__)

JSON_RPC_URL = "https://s.altnet.rippletest.net:51234/"

async def transfer_nft(body):
    wallet_seed = body.get("wallet_seed")
    nft_id = body.get("nft_id")
    nft_offer_price = (body.get("nft_offer_price"))
    transfer_to_wallet_seed = body.get("transfer_to_wallet_seed")
    
    if not all([wallet_seed, nft_id, nft_offer_price,transfer_to_wallet_seed]):
        return HTTPException(status_code=400, detail="Missing required input data.")
    
    checkNFTDB = await MongoHelper.find_one("documents", {"meta.nftoken_id": nft_id})
    if checkNFTDB and checkNFTDB["Account"] != "":
        logger.info("Requesting address from the faucet...")
        
        # Create XRPL client
        client = AsyncJsonRpcClient(JSON_RPC_URL)
        
        # Generate XRPL wallet from seed
        issuer_wallet = Wallet(seed=wallet_seed, sequence=0)
        issuerAddr = issuer_wallet.classic_address
        
        transferto_wallet = Wallet(seed=transfer_to_wallet_seed, sequence=0)
        transfertoAddr = transferto_wallet.classic_address
        
        logger.info(f"Issuer Account: {issuerAddr}")

        # Construct a NFTokenCreateOffer transaction to sell the previously minted NFT on the open market
        logger.info(f"Selling NFT {nft_id} for {int(nft_offer_price) / 1000000} XRP on the open market...")
        sell_1_tx = NFTokenCreateOffer(
            account=issuerAddr,
            nftoken_id=nft_id,
            amount=str(nft_offer_price), # 10 XRP in drops, 1 XRP = 1,000,000 drops
            flags=NFTokenCreateOfferFlag.TF_SELL_NFTOKEN,
        )

        # Sign sell_1_tx using minter account
        sell_1_tx_signed = await safe_sign_and_autofill_transaction(transaction=sell_1_tx, wallet=issuer_wallet, client=client)
        sell_1_tx_signed = await send_reliable_submission(transaction=sell_1_tx_signed, client=client)
        sell_1_tx_result = sell_1_tx_signed.result
        logger.info(f"Sell Offer tx result: {sell_1_tx_result['meta']['TransactionResult']}"
            f"\nTx response: {sell_1_tx_result}")

        # Query the sell offer
        response = await client.request(
            NFTSellOffers(
                nft_id=nft_id
            )
        )

        offer_objects = response.result

        # Construct a NFTokenAcceptOffer offer to buy the NFT being listed for sale on the open market
        logger.info(f"Accepting offer {offer_objects['offers'][0]['nft_offer_index']}...")
        buy_tx = NFTokenAcceptOffer(
            account=transfertoAddr,
            nftoken_sell_offer=offer_objects['offers'][0]['nft_offer_index']
        )

        # Sign buy_tx using buyer account
        buy_tx_signed = await safe_sign_and_autofill_transaction(transaction=buy_tx, wallet=transferto_wallet, client=client)
        buy_tx_signed = await send_reliable_submission(transaction=buy_tx_signed, client=client)
        buy_tx_result = buy_tx_signed.result
        
        await MongoHelper.insert(buy_tx_result, "document_transfers")
        await MongoHelper.updateOne("documents", {"meta.nftoken_id": nft_id}, {"last_transfer" : buy_tx_result, "transfered" : True})
        
        logger.info(f"Buy Offer result: {buy_tx_result['meta']['TransactionResult']}"
            f"\nTx response: {buy_tx_result}")
        
        return True
    return False

async def burn_nft(body):
    wallet_seed = body.get("wallet_seed")
    nft_id = body.get("nft_id")

    if not all([wallet_seed, nft_id]):
        return HTTPException(status_code=400, detail="Missing required input data.")
    
    checkNFTDB = await MongoHelper.find_one("documents", {"meta.nftoken_id": nft_id})
    if checkNFTDB and checkNFTDB["Account"] != "":
        if checkNFTDB.get("burned") is not None and checkNFTDB["burned"] == True :
            logger.info("NFT token is already burned.")
            return True
        else:
            logger.info("Requesting address from the faucet...")
            
            # Create XRPL client
            client = AsyncJsonRpcClient(JSON_RPC_URL)
            
            # Generate XRPL wallet from seed
            issuer_wallet = Wallet(seed=wallet_seed, sequence=0)
            issuerAddr = issuer_wallet.classic_address
            
            logger.info(f"Issuer Account: {issuerAddr}")

            logger.info(f"Burning NFT : {nft_id}...")
            
            burn_tx = NFTokenBurn(
                account=issuerAddr,
                nftoken_id=nft_id
            )

            burn_tx_signed = await safe_sign_and_autofill_transaction(transaction=burn_tx, wallet=issuer_wallet, client=client)
            burn_tx_signed = await send_reliable_submission(transaction=burn_tx_signed, client=client)
            burn_tx_result = burn_tx_signed.result

            if burn_tx_result['meta']['TransactionResult'] == "tesSUCCESS":
                await MongoHelper.updateOne("documents", {"meta.nftoken_id": nft_id}, {"burned" : True})
                logger.info(f"Transaction was successfully validated, NFToken {burn_tx_result['NFTokenID']} has been burned")
                return True
            else:
                await MongoHelper.updateOne("documents", {"meta.nftoken_id": nft_id}, {"burned" : False})
                logger.error(f"Transaction failed, NFToken was not burned, error code: {burn_tx_result['meta']['TransactionResult']}")
                return False


async def mint_nft(body):
    wallet_seed = body.get("wallet_seed")
    fee = str(body.get("fee"))
    transfer_fee = int(body.get("transfer_fee"))
    metadata = encrypt_sensitive_data(str(body.get("metadata")))
    url = body.get("url")
    uuid = body.get("uuid")

    if not all([wallet_seed, fee, transfer_fee, metadata, url, uuid]):
        raise HTTPException(status_code=400, detail="Missing required input data.")
    
    logger.info("Requesting address from the faucet...")
    
    # Create XRPL client
    client = AsyncJsonRpcClient(JSON_RPC_URL)
    
    # Generate XRPL wallet from seed
    issuer_wallet = Wallet(seed=wallet_seed, sequence=0)
    issuerAddr = issuer_wallet.classic_address
    
    logger.info(f"Issuer Account: {issuerAddr}")

    # Construct NFTokenMint transaction to mint 1 NFT
    logger.info(f"Minting a NFT...")
    
    MEMO_DATA_HEX = uri_hex(url)
    
    mint_tx = NFTokenMint(
        account=issuerAddr,
        nftoken_taxon=1,
        flags=NFTokenMintFlag.TF_TRANSFERABLE,
        transfer_fee=transfer_fee,
        uri=MEMO_DATA_HEX,
        memos=[xrpl.models.transactions.Memo(
            memo_data=memo_json_to_hex(metadata)
        )]
    )

    # Sign mint_tx using the issuer account
    mint_tx_signed = await safe_sign_and_autofill_transaction(transaction=mint_tx, wallet=issuer_wallet, client=client)
    mint_tx_signed = await send_reliable_submission(transaction=mint_tx_signed, client=client)
    mint_tx_result = mint_tx_signed.result
    
    mint_tx_result["UUID"] = uuid
    
    await MongoHelper.insert(mint_tx_result, "documents")
    
    logger.info(f"Minting a NFT Done!")
    
    return mint_tx_result


async def get_nft_status(UUID: str):
    checkNFTDB = await MongoHelper.find_one("documents", {"UUID": UUID}, {'_id': False, 'last_transfer' : False})
    if checkNFTDB and checkNFTDB["Account"] != "":
            #del checkNFTDB['_id'];
            return checkNFTDB
    else:
        return False
    


async def get_nft_details(token_id: str):
    
    checkNFTDB = await MongoHelper.find_one("documents", {"meta.nftoken_id": token_id})
    if checkNFTDB and checkNFTDB["Account"] != "":
        try:
            response = requests.post(
                url=JSON_RPC_URL,
                headers={
                    "Content-Type": "application/json",
                },
                data=json.dumps({
                    "method": "tx",
                    "params": [
                        {
                            "transaction": checkNFTDB["hash"],
                            "binary": False
                        }
                    ]
                })
            )
            if response.status_code == 200 :
                return response.json()
        except requests.exceptions.RequestException:
            return False
    else:
        return False
    
    
async def get_nft_details_decrypt(token_id: str):
    
    checkNFTDB = await MongoHelper.find_one("documents", {"meta.nftoken_id": token_id})
    if checkNFTDB and checkNFTDB["Account"] != "":
        try:
            response = requests.post(
                url=JSON_RPC_URL,
                headers={
                    "Content-Type": "application/json",
                },
                data=json.dumps({
                    "method": "tx",
                    "params": [
                        {
                            "transaction": checkNFTDB["hash"],
                            "binary": False
                        }
                    ]
                })
            )
            
            # Decryption should happen here
            if response.status_code == 200 :
                respData = response.json()
                respDataResult = respData["result"];
                respDataResult["URI"] = (hex_to_ascii(respDataResult["URI"]))
                respDataResultMemos = respDataResult["Memos"];
                for entry in respDataResultMemos:
                    decryptHex = hex_to_ascii(entry["Memo"]["MemoData"]);
                    entry["Memo"]["MemoData"] = decrypt_sensitive_data(decryptHex)
                return respData;
        except requests.exceptions.RequestException:
            return False
    else:
        return False
    
    
    
    
