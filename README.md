### XRPL Blockchain in Ecommerce POC

This codebase shows how we can use blockchain in ecommerce. As examples, this POC includes usage of blockchain in customer verification in blockchain and product warranty verification.

#### Run xrpl_api

```sh
$ pip install -r requirements.txt
$ uvicorn main:app --workers 4 --reload
```

#### Run poc_shop

- update the .env file
- docker-compose up -d --build
- If your runtime is apple silicon, use `docker-compose -f docker-compose-m1.yml up -d --build` command
- RUN `docker exec -it app bash`
- Inside the container, run `composer install && chmod -R 777 storage/ bootstrap/cache/`
- Inside the container, run `php artisan migrate --seed`
- While inside the container, compile the assets with `npm i && npm run dev`
- While inside the container, link the images `php artisan storage:link`
- OPEN [http://localhost]

### Admin dashboard
In order to enter the administration dashboard, you have to hit the `/admin` route. 
E.g enter http://localhost/admin or in general http://your-domain/admin in your browser.

If you're not already logged in, you are redirected to the admin login screen.
There you can use one of the following credentials to access the admin dashboard.

**Email and Passwords**

```php
john@doe.com / secret (role:superadmin)
admin@doe.com / secret (role:admin)
clerk@doe.com / secret (role:user)
```
