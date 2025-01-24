# NEWS AGGREGATOR FE

### Before proceeding, ensure you have the following installed on your system:

    Docker and Docker Compose
        Download and install Docker: https://www.docker.com/.
    
    Git
        For cloning the repository.
        Download Git: https://git-scm.com/.

### Steps to Set Up the Application

    1. Clone Repository 
        git clone git@github.com:ah3eyy/news-aggregator-fe.git
        cd your-repo-name

    2. Create an Environment File and Install 
       Create a .env file in the root of the project. This file contains environment variables used by the larevel application.
       cp .env.example .env
        
       After creating .env 
        composer install

    3. Set Permission to enable app write to log
         cd {root of application}
         sudo chmod -R 775 storage
         sudo chown -R $(whoami):staff storage
        
         Run the following artisan command
             php artisan cache:clear
             php artisan config:clear
             php artisan config:cache
             php artisan view:clear
             php artisan view:cache
             php artisan install:api --passport
             php artisan passport:client

    4. Build and Run with Docker
       Ensure Docker is running on your machine, then proceed with the following:

       Build Docker Image
         docker compose build

       Run Docker
         docker compose up -t
    
    5. Application ready on http://127.0.0.1:8000
    
    6. Run php artisan app:spool-article spool command 

    Note : The schedule command is set to run twice daily 

