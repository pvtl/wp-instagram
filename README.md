
# WP Instagram

A WordPress plugin that allows you to connect to the [Instagram Basic Display API](https://developers.facebook.com/docs/instagram-basic-display-api/). The Basic Display API replaces the Instagram Legacy API.

## Installation (Bedrock)

```bash
# 1. Get it ready (to use a repo outside of packagist)
composer config repositories.wp-instagram git https://bitbucket.org/pvtl/wp-instagram

# 2. Install the Plugin
composer require pvtl/wp-instagram
```

## Before You Start

You will need:

- A  [Facebook Developer Account](https://developers.facebook.com/apps).
- An  [Instagram account](https://www.instagram.com/)  with media.

## Getting Started

### Create a [Facebook App](https://developers.facebook.com/docs/instagram-basic-display-api/getting-started)

Go to [developers.facebook.com](https://developers.facebook.com/), click **My Apps**, and create a new app. Once you have created the app and are in the App Dashboard, navigate to **Settings** > **Basic**, scroll the bottom of page, and click **Add Platform**.

Choose  **Website**, add your website’s URL, and save your changes. You can change the platform later if you wish, but for this tutorial, use  **Website**.

Click **Products**, locate the **Instagram** product, and click **Set Up** to add it to your app.

Click  **Basic Display**, scroll to the bottom of the page, then click  **Create New App**.

In the form that appears, complete each section using the guidelines below.

#### Display Name

Enter the name of the Facebook app you just created.

#### Valid OAuth Redirect URIs

From within WordPress, navigate to **Instagram** > **Login**. Copy the OAuth Redirect URI and enter it in the Valid OAuth Redirect URIs field. *(Callback URIs must be https)*.

For example:  `https://example.com/wp-content/plugins/wp-instagram/admin/callback.php`

#### Deauthorize Callback URL

Enter your website’s URL.

#### Data Deletion Request Callback URL

Enter your website’s URL once again.

#### App Review

Skip this section for now since you will not be switching the app to Live Mode.

### Connect an Instagram Account

From within the Facebook app, navigate to **Roles** > **Roles** and scroll down to the Instagram Testers section. Click **Add Instagram Testers** and enter your Instagram account’s username and send the invitation.

Open a new web browser and go to  [www.instagram.com](https://www.instagram.com/accounts/manage_access/)  and sign into your Instagram account that you just invited. Navigate to  **(Profile Icon)**  >  **Edit Profile**  >  **Apps and Websites**  >  **Tester Invites**  and accept the invitation.

Your Instagram account is now eligible to be accessed by your Facebook app while it is in Development Mode.

### Connect WordPress

From within the Facebook App, navigate to **Products** > **Instagram** > **Basic Display**. Copy the **Instagram App ID** and **Instagram App Secret**.

From within WordPress, navigate to navigate to **Instagram** > **Login**. Enter the **Instagram App ID** and **Instagram App Secret** values into the **Client ID** and **Client Secret** fields and click **Save Changes**.  

Click the **Login to Instagram** button and allow WordPress to access the Instagram account.

Once logged in from WordPress successfully, you should see the **Sync Media Now** checkbox, check this and Save Changes once again to manually fetch media from the API.

## The `[instagram]` Shortcode

`[instagram]` will output Instagram media in a grid. It has a few arguments that allow you to change the layout without overriding the default template and styles.

- `total` - The total amount of posts to show.
- `gutter` - The size of the gutter between posts in rems.
- `styles` - Setting this to false will prevent outputting default styles.
- `xl, lg, md, sm, xs` - Allows you to set the desired grid columns per row.

## WordPress Templating

You can override the default plugin template by creating an override file in your WordPress theme.

- Create a directory in the base directory of your theme called `instagram`.
- Create the override file (e.g. `media.php`) within the `instagram` directory.
- The file will now be used instead of the default template.

### `media.php`

`media.php` loops through Instagram posts that have synced to WordPress. Override this template to adjust the grid/post markup.

### `style.php`

`style.php` contains the default grid styles. You can override this template to adjust styling, alternatively you can prevent the output of default styles by setting `[instagram styles=false]` and add your own styles in your theme stylesheet.