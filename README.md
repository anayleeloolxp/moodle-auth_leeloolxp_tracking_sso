# Leeloo LXP SSO

This plugin is a part of the Moodle LMS | Leeloo LXP integration

It enables your Moodle LMS to be seamlessly integrated with your Leeloo LXP.

It acts as a Login and Identity provider for Leeloo LXP.

How does it work?

If the Moodle SSO option is enabled in Leeloo LXP, whenever users try to access the Leeloo LXP login page, they will be
redirected to Moodle LMS login page. Upon entering their credentials in Moodle LMS, they are redirected to Leeloo LXP with a
login key.


Upon log in to Moodle LMS:

- If the user doesn’t exist in Leeloo LXP, it will create an account and a unique login key

- If the user already exists in Leeloo LXP, it will update the user’s information