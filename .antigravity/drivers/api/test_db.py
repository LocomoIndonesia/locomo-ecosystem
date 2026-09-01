import os
import sys

# Ensure pymssql is installed
try:
    import pymssql
except ImportError:
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "pymssql"])
    import pymssql

server = "sqlmisis-test.ca87cd4bc197.database.windows.net"
user = "OAMS_DEV_USER"
password = "OAMSDevUser2024$!"
database = "OAMS_DB"

print("Connecting to SQL Server inside container...")
try:
    conn = pymssql.connect(server=server, user=user, password=password, database=database, timeout=10)
    cursor = conn.cursor()
    
    # 1. Fetch SP definition
    print("Successfully connected! Fetching SP_IZIN definition...")
    cursor.execute("SELECT definition FROM sys.sql_modules WHERE object_id = OBJECT_ID('dbo.SP_IZIN')")
    row = cursor.fetchone()
    if row:
        print("\n=== SP_IZIN Definition ===")
        print(row[0])
    else:
        print("SP_IZIN not found in sys.sql_modules")
        
    conn.close()
except Exception as e:
    print("Connection failed:", e)
