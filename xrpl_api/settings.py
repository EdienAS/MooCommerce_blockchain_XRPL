from dotenv import load_dotenv
import os

load_dotenv()

APP_HOST_NAME= os.getenv("APP_HOST_NAME")
MONGODB_CONNECTION = os.getenv("MONGODB_CONNECTION")
MONGODB_DB = os.getenv("MONGODB_DB")