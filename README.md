# Pokémon Symfony

This is a small project to catch Pokémons in the wild!

The idea is to get into Symfony framework and create a simple application using some good coding practices.
In this application I used [Pokémon TGC SDK](https://github.com/PokemonTCG/pokemon-tcg-sdk-php) as a service to communicate with the Pokémon TGC API [pokemontcg.io](http://pokemontcg.io/).

This a Docker based project for Symfony framework with PHP 8.3

## Getting Started 

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)


2. Clone repository `git clone https://github.com/renatoamado/pokemon-symfony.git` and go to the directory


3. **Inside the terminal** 
   - Run `docker compose build --no-cache` to build fresh images
   - Run `docker compose up -d --wait` to set up and start a fresh Symfony project


4. Access the application container `docker exec -it php bash`


5. Copy the local and test local env files
   - `cp .env.local.example .env.local`
   - `cp .env.test.local.example .env.test.local`
   - in these files are configurations like **DATABASE_URL** and **API_KEY**. The first one is something that maybe I did something unnecessary in some point
   but now this variable is necessary :). And the api key is not required, but it will limit the amount of requests you can make to the Pokémon API. 
   If you need one, just create an account at [Pokémon TGC](https://pokemontcg.io/) and when you confirm your email, the apikey will be available once you log in again :)


6. Install composer dependencies `composer install`


7. Open `https://localhost:8080/pokemon` in your favorite web browser and wait a little bit (yes, the first fetch is slow)


8. There's a test suite configured in `composer.json` file you can use it inside the container as follows
    - `composer refacto` to run **rector process**. It will validate and refactor your code based on your current PHP version
    - `composer lint` to run **php-cs-fixer fix**. It will apply the phpcs to your code base and fix your code based on the config file in the project root folder
    - `composer test:lint` to run **php-cs-fixer fix --diff --verbose --dry-run**. It will run phpcs fixer, but it will only show the suggestions. It will not change the code
    - `composer test:types` to run **phpstan analyse --ansi --memory-limit=2G**. It will run phpstan on your code to check for wrong variable types
    - `composer test:unit` to run **phpunit --coverage-text**. It will run all your tests inside the tests folder and show the code coverage %
    - `composer test:refacto` to run **rector process --dry-run**. It will run rector, but it will just show the suggested changes
    - `composer test` to run the complete test suite above

 
## Observations
   - When you run the phpcs tests, either `composer test:lint` or `composer test`, phpcs fixer might show some "errors" which are really nothing. 
   If that happens, just run `composer lint` and it will fix all of them and you can run the complete test suite again 
   - Whenever you run the unit tests, the cache will be cleaned the application will have to make the call to the api again to list all cards, and it will take a while :( 

## **Have Fun!**