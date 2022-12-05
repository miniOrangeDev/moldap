# LDAP/Active Directory Integration plugin for Craft CMS 3.x or 4.x

LDAP/Active Directory Integration

![Screenshot](resources/img/miniorange.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require miniorangedev/moldap

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for miniOrange LDAP/Active Directory Integration.

## LDAP/Active Directory Integration Overview

**miniOrange LDAP / Active Directory Integration** Plugin for Craft CMS allows you to login into your website using the credentials which are stored in your LDAP Server / Active Directory. 
This plugin provides a simple and secure method of LDAP authentication that is suitable for users with personal websites as well as enterprises with a significant number of users saved in their Active Directory.

**The LDAP Authentication can be performed on various Active Directory / Other LDAP Server such as:**
1. Microsoft Active Directory
2. Azure Active Directory
3. Sun Active Directory
4. OpenLDAP Directory
5. JumpCloud
6. FreeIPA Directory
7. Synology
8. OpenDS and other LDAP directories.

## Configuring LDAP/Active Directory Integration

Just add a couple of lines in your Login twig template and Users will be able to login using LDAP/ Active Directory credentials
Replace the actionInput with the code given into the required .twig file.<br>
```{{ actionInput('moldap/login/ldaplogin') }}```

## LDAP/Active Directory Integration Features

+ **LDAP Authentication** : Enable LDAP Authentication for any users present in your Active Directory/LDAP Servers into the Craft Website. This plugin is compatible with most of the directories supporting LDAP protocol i.e. Microsoft Active Directory, Azure Active Directory Domain Services (LDAPS), OpenLDAP, FreeIPA and many more.

+ **Login With Any LDAP Attribute of Your Choice** : Authenticate users against any one LDAP/AD username attributes like sAMAccountName, UID, UserPrincipalName, mail, cn or any other custom LDAP attribute(s) of your choice.
 
+ **Automatic User Registration in Craft** : Automatically register / create users in Craft on the first login of LDAP users in the Craft website.
 
+ **Authenticate Users from LDAP and Craft** : Allow all users on your Craft Website to log in even if they are not present in the LDAP/Active Directory.
 
+ **LDAPS (LDAP Secure Connection) support** : Supports secure connection between Craft and Active Directory/LDAP server via LDAPS protocol, this ensures protection against credential theft.
 
+ **User Profile Sync upon Login** : Keep the Craft User’s profile information in sync with the Active Directory/other LDAP Directories upon authentication.

+ Test connection to your Active Directory/other LDAP Directory while configuring LDAP server information in the plugin.

+ Test authentication using credentials stored in your Active Directory/other LDAP Directory after configuring LDAP server information in the plugin with the latest versions of Craft.

+ Compatible with the Craft versions (**3.x and above**) and latest version of **PHP**.

#### Premium Features : 
+ **Authenticate Users from Multiple LDAP Search Bases** : Authenticate users against multiple search bases (organizational units) from your Active Directory/other LDAP Directories.

+ **Attribute Mapping** : Map the LDAP/Active Directory attributes to the Craft user profile and sync upon every successful LDAP / AD Login. Configure and fetch the LDAP/AD attributes such as UID, cn (common name), mail, telephoneNumber, givenName, sn, sAMAccountName.

+ **Multiple Username attributes** :  Authenticate users against multiple LDAP/AD username attributes like sAMAccountName, UID, UserPrincipalName, mail, cn or any other custom LDAP attribute(s) of your choice.

+ **Restrict / Allow local craft users to Login** : You can allow or restrict the access to users present in your local crafts website and not in Active Directory / LDAP Server.

+ **Priority Support** : Get high priority support from our team of dedicated developers on purchase of premium plugins for any technical issues you face.
 
Check out our [website](miniorange.com) for other products we offer.
 
If you have any queries or need any sort of assistance , you can reach out to us at [ldapsupport@xecurify.com](ldapsupport@xecurify.com) or [Contact Us](https://www.miniorange.com/contact). Our customer support team is available **24x7** to assist you in any way possible.