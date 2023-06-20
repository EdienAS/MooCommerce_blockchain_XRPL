import uvicorn
from fastapi import FastAPI, HTTPException, BackgroundTasks
from starlette.concurrency import run_in_threadpool
from fastapi.requests import Request
import logging

# setup loggers
logging.config.fileConfig('logging.conf', disable_existing_loggers=False)

from utils.xrpl_utils import get_nft_status,mint_nft,get_nft_details,get_nft_details_decrypt,burn_nft,transfer_nft

# Initialize FastAPI app
app = FastAPI()

@app.post("/api/mint-nft")
async def create_nft(request: Request, background_tasks: BackgroundTasks):
    body = await request.json()
    background_tasks.add_task(mint_nft, body)
    return {"status" : "ok", "msg" : "NFT minting is under processing."}


@app.post("/api/burn-nft")
async def burn_nft_fn(request: Request, background_tasks: BackgroundTasks):
    body = await request.json()
    background_tasks.add_task(burn_nft, body)
    return {"status" : "ok", "msg" : "NFT burning is under processing."}

@app.post("/api/transfer-nft")
async def transfer_nft_fn(request: Request, background_tasks: BackgroundTasks):
    body = await request.json()
    background_tasks.add_task(transfer_nft, body)
    return {"status" : "ok", "msg" : "NFT treansfer is under processing."}

@app.get("/api/nft/status/{UUID}")
async def get_nft_stat(UUID: str):
    token_details = await get_nft_status(UUID)
    return token_details

@app.get("/api/nft/{token_id}")
async def get_nft(token_id: str):
    token_details = await get_nft_details(token_id)
    return token_details

@app.get("/api/nft/decrypt/{token_id}")
async def get_nft(token_id: str):
    token_details = await get_nft_details_decrypt(token_id)
    return token_details


if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)
