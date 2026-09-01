import pymssql

server = "sqlmisis-test.ca87cd4bc197.database.windows.net"
user = "OAMS_DEV_USER"
password = "OAMSDevUser2024$!"
database = "OAMS_DB"

print("Connecting to database...")
try:
    conn = pymssql.connect(server=server, user=user, password=password, database=database, timeout=10)
    cursor = conn.cursor()
    
    # 1. Fetch SP_USER
    cursor.execute("SELECT definition FROM sys.sql_modules WHERE object_id = OBJECT_ID('dbo.SP_USER')")
    row = cursor.fetchone()
    if row:
        print("\n=== SP_USER Definition ===")
        print(row[0])
    else:
        print("SP_USER not found")
        
    # 2. Fetch SP_GET_MENU
    cursor.execute("SELECT definition FROM sys.sql_modules WHERE object_id = OBJECT_ID('dbo.SP_GET_MENU')")
    row = cursor.fetchone()
    if row:
        print("\n=== SP_GET_MENU Definition ===")
        print(row[0])
    else:
        print("SP_GET_MENU not found")
        
    conn.close()
except Exception as e:
    print("Failed to query database:", e)
