from pathlib import Path
import json
import codecs
import hashlib


from dotenv import load_dotenv
from motor.motor_asyncio import AsyncIOMotorClient
from cryptography.fernet import Fernet

# Load environment variables from .env file
load_dotenv()

# Set app encryption key (replace with your secure key)
app_encryption_key = "0GdSe2zh8KcdE8zPw12GcP7go0QJvOcDD6DOVXCOLNA="

def to_sha1(input):
    h = hashlib.sha1(input.encode('utf-8'))
    output = h.hexdigest().upper()
    print("output length ", len(output))
    return output


def to_hex(input_string):
    output_hex = input_string.encode('utf-8').hex().upper()
    return output_hex


def file_to_hex(filename):
    with Path(filename).open("rb") as file:
        input = json.load(file)
        text = json.dumps(input, sort_keys=True,
                          indent=4, separators=(',', ': '))
        hex_value = to_hex(text)
        return hex_value


def memo_to_hex(file_url, meta_url):
    data = {
        'file_url': file_url,
        'metadata_url':  meta_url
    }

    return to_hex(str(data))


def uri_hex(file_url):
    data = file_url
    return to_hex(str(data))


def memo_json_to_hex(data_json):
    data = data_json
    return to_hex(str(data))


def hex_to_ascii(hex_string):
    binary_str = codecs.decode(hex_string, "hex")
    string_value = str(binary_str, 'utf-8').replace("b'", "").replace("'", "")
    return string_value


def get_explorer_addr(account):
    return f"https://test.bithomp.com/explorer/{account}"


def encrypt_sensitive_data(data: str) -> str:
    # Generate encryption key from app_encryption_key
    encryption_key = Fernet(app_encryption_key)

    # Encrypt data
    encrypted_data = encryption_key.encrypt(data.encode("utf-8"))

    # Return encrypted data
    return encrypted_data


def decrypt_sensitive_data(data: str) -> str:
    # Generate decryption key from app_encryption_key
    decryption_key = Fernet(app_encryption_key)
    # Decrypt data
    decrypted_data = decryption_key.decrypt(data).decode()

    # Return decrypted data
    return decrypted_data