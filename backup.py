from pydrive.auth import GoogleAuth
from pydrive.drive import GoogleDrive
from datetime import datetime
import os
import shutil


gauth = GoogleAuth()
# Try to load saved client credentials
# Still need verifcation for the first time. Ignore the warning in cmd.
gauth.LoadCredentialsFile("/var/www/html/itflow/creds.txt")
if gauth.credentials is None:
    # Authenticate if they're not there
    gauth.LocalWebserverAuth()
elif gauth.access_token_expired:
    # Refresh them if expired
    gauth.Refresh()
else:
    # Initialize the saved creds
    gauth.Authorize()
# Save the current credentials to a file
gauth.SaveCredentialsFile("/var/www/html/itflow/creds.txt")
drive = GoogleDrive(gauth)

now = datetime.now()
dt_string = now.strftime("%d/%m/%Y %H:%M:%S")

directory_path = '/var/lib/mysql'  # db path
destination_path = 'backup'  # db zip location (use /home/<user>/Downloads if you want the file to be save locally)
archive_name = 'db'

os.chmod('/var/lib/mysql', 0o777)

# Provide the format 'zip' when calling make_archive
shutil.make_archive(
    os.path.join(destination_path, archive_name),
    'zip',  # Specify the archive format
    directory_path
)

os.chmod('backup/db.zip', 0o777)

file = drive.CreateFile({'title': 'DB - ' + dt_string + '.zip', 'parents': [{'id': '1XBNScG51PFP3bYOUbJ9uZCBsh_o6sU5B'}]}) #id is the last part of your google drive url 
file.SetContentFile('backup/db.zip')
file.Upload()

directory_path2 = '/var/www/html/itflow/uploads/expenses'  # receipts folder path
destination_path2 = 'backup'  # db zip location
archive_name2 = 'receipts'

os.chmod('/var/www/html/itflow/uploads/expenses', 0o777)

# Provide the format 'zip' when calling make_archive
shutil.make_archive(
    os.path.join(destination_path2, archive_name2),
    'zip',  # Specify the archive format
    directory_path2
)

os.chmod('backup/receipts.zip', 0o777)

file2 = drive.CreateFile({'title': 'receipts - ' + dt_string + '.zip', 'parents': [{'id': '1XBNScG51PFP3bYOUbJ9uZCBsh_o6sU5B'}]})
file2.SetContentFile('backup/receipts.zip')
file2.Upload()

