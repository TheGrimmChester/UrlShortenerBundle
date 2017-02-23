![AWHS Logo](https://nicolasmeloni.ovh/images/awhspanel.png)

# UrlShortenerBundle
UrlShortenerBundle of AWHSPanel

This package provides an easy way to create shortened URLs.

## Requirements
* Have installed the [foundation](https://github.com/TheGrimmChester/AWHSPanel/blob/master/README.md)  
* (Optional) Have installed the [UserBundle](https://github.com/TheGrimmChester/UserBundle/blob/master/README.md)  
    This bundle will be used when implementing data tracking.
    
## Installation
1. Clone this bundle inside `src/AWHS/` directory.
2. Enable the bundle in the kernel by adding the following line right after `//AWHS Bundles` in `app/AppKernel.php` file:  
`new AWHS\UrlShortenerBundle\AWHSUrlShortenerBundle(),`
3. Append the following configuration to the `app/routing.yml` file:  
```yaml
awhs_url_shortener:
    resource: "@AWHSUrlShortenerBundle/Resources/config/routing.yml"
    prefix:   /
```
4. For this first iteration, replace all `your_domain.com` with your own domain name.  
5. Update database & clear cache: `php bin/console doctrine:schema:update --force; php bin/console cache:clear; php bin/console cache:clear --env=prod`  
You may have to set permissions back to www-data `chown -R www-data:www-data /usr/local/awhspanel/panel/*`

## TODO
- [ ] Configuration file
- [ ] Multilingual
- [ ] Add users & data tracking