# safe-to-delete
This is a WordPress plugin that will query the database and make sure your template file is safe to delete (not being used).

#Usage
1. Install the plugin.
2. Go to Tools > Safe To Delete.
3. Type name of filename in input box and hit "Let's check..."
4. Results will appear below.

#How it Works
The plugin queries the postmeta table for the filename. If the filename is not found, it will clear the file to be deleted! If the filename is found, it will list all of the pages that are used by the file with links to view it or edit it.