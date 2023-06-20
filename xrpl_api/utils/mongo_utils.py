from typing import List

from fastapi import HTTPException
from pymongo import MongoClient
from pymongo.collection import Collection
from pymongo.errors import PyMongoError


async def get_mongo_collection(db_name: str, collection_name: str) -> Collection:
    client = MongoClient()
    db = client[db_name]
    collection = db[collection_name]
    return collection


async def find_document(collection: Collection, filter: dict) -> dict:
    try:
        document = await collection.find_one(filter)
        if document:
            return document
        else:
            raise HTTPException(status_code=404, detail="Document not found")
    except PyMongoError as e:
        raise HTTPException(status_code=500, detail=str(e))


async def update_document(collection: Collection, filter: dict, update: dict) -> dict:
    try:
        document = await collection.find_one_and_update(filter, {"$set": update}, return_document=True)
        if document:
            return document
        else:
            raise HTTPException(status_code=404, detail="Document not found")
    except PyMongoError as e:
        raise HTTPException(status_code=500, detail=str(e))


async def delete_document(collection: Collection, filter: dict) -> dict:
    try:
        document = await collection.find_one_and_delete(filter)
        if document:
            return document
        else:
            raise HTTPException(status_code=404, detail="Document not found")
    except PyMongoError as e:
        raise HTTPException(status_code=500, detail=str(e))


async def find_documents(collection: Collection, filter: dict) -> List[dict]:
    try:
        documents = await collection.find(filter).to_list(None)
        if documents:
            return documents
        else:
            raise HTTPException(status_code=404, detail="No documents found")
    except PyMongoError as e:
        raise HTTPException(status_code=500, detail=str(e))
