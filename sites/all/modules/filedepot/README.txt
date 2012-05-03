
The Filedepot Document Management module satisfies the need for a full featured document management module supporting role or user based security.
 - Documents can be saved outside the Drupal public directory to protect corporate documents for safe access and distribution.
 - Intuitive and convenient combination of features and modern AJAX driven Web 2.0 design provides the users with the google docs like interface
 - Flexible permission model allows you to delegate folder administration to other users.
 - Setup, view, download and upload access for selected users or roles. Any combination of permissions can be setup per folder.
 - Integrated MS Windows desktop client is available from Nextide and allows users to easily upload one or 100's of files directly to the remote web-based document repository.
   Simply drag and drop files from their local desktop and they are uploaded.
   Files will appear in an Incoming Queue inside the filedepot module allowing the user to move them one at a time or in batches to their target folder.
 - Cloud Tag and File tagging support to organize and search your files.
   Document tagging allows users to search the repository by popular tags.
   Users can easily see what tags have been used and select one of multiple tags to display a filtered view of files in the document repository.
 - Users can upload new versions of files and the newer file will automatically be versioned. Previous versions are still available.
   When used with the desktop client, users can have the hosted file in filedepot automatically updated each time the user saves the file - online editing.
 - Users can selectively receive notification of new files being added or changed.
   Subscribe to individual file updates or complete folders. File owner can use the Broadcast feature to send out a personalized notification.
 - Convenient reports to view latest files, most recent folders, bookmarked files, locked or un-read files.
 - Users can flag document as 'locked' to alert users that it is being updated.

The Filedepot module is provided by Nextide www.nextide.ca and written by Blaine Lang (blainelang)


Dependencies
------------
 * Content, FileField
 * libraries

 Organic Groups is not required but if you install the modules 'og' and 'og_access',
 then you will be able to manage folder access via organic groups.
 The module will automatically detect if both modules are enabled and will only
 show the group options for permissions once both 'og' and 'og_access' modules are enabled.

Requirements
------------
 PHP 5.2+ and PHP JSON library enabled.
 Flash 10.x Addon installed in the browser.

 As of PHP 5.2.0, the JSON extension is bundled and compiled into PHP by default.


Install
-------

1) Copy the filedepot folder to the modules folder in your installation.

2) The filedepot module now requires the libraries module be installed.
   We are not permitted to include NON-GPL or mixed license files in the module distribution as per Drupal guidelines.

   You will now need to create a sites/all/libraries folder if you don't already have the libraies module installed.
   PLEASE rename the files as noted below
   
   The following three javascript and support files then need to be retrieved and saved to the sites/all/libraies folder.
   > http://www.strictly-software.com/scripts/downloads/encoder.js  - SAVE FILE as: html_encoder.js
   > http://yuilibrary.com/support/2.8.2/dropin_patches/uploader-2.7.0.zip  - SAVE FILE as: yui_uploader.swf
   > http://jquery.malsup.com/block/#download  - SAVE FILE as jquery.blockui.js

3) Enable the module using Administer -> Site building -> Modules
   (/admin/build/modules).

   The module will create a new content type called 'filedepot folder'

4) Review the module settings using Administer -> Site configuration -> Filedepot Settings
   Save your settings and at a minium, reset to defaults and save settings.

5) Access the module and run create some initial folders and upload files
   {siteurl}/index.php?q=filedepot

6) Review the permissions assigned to your site roles: {siteurl}/index.php?q=admin/user/permissions
   Users will need atleast 'access filedepot' permission to access filedepot and to view/download files.

Notes:
a)  You can also create new folders and upload files (attachments) via the native Drupal Content Add/View/Edit interface.
b)  A new content type is automatically created 'filedepot folder'. When adding the very first folder, the content type
    will be modified to programtically add the the CCK filefield type for the files or attachements.
    It is not possible to execute the CCK import to modify the content type during the install as the module has to be first active.
c)  You can setup filedepot to not load the YUI libraries remotely from Yahoo via the module admin settings page.
    Set the baseurl to be a local URL and download the YUI libraries from http://yuilibrary.com/downloads/#yui2
    Only version 2.7.0 of the libraries is supported at present.

    You can also load the YUI libraries from Google:
    http://ajax.googleapis.com/ajax/libs/yui/2.7.0/build/

    Only google supports loading them using a https URL
    https://ajax.googleapis.com/ajax/libs/yui/2.7.0/build/

