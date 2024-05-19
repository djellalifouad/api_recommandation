# Product Recommendation API

## Setup


1. **Install dependencies:**
    ```bash
    composer install
    ```

2. **Set up environment variables:**
    Create a `.env.local` file and add your WeatherAPI key:
    ```env
    WEATHER_API_KEY=your_api_key_here
    ```

3. **Set up the database:**
    - Create the SQLite database file:
        ```bash
        touch var/data.db
        ```
    - Update your `.env.local` to use SQLite:
        ```env
        DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
        ```

4. **Run database migrations:**
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

5. **Load fixtures (optional):**
    If you have fixtures to load initial data, run:
    ```bash
    php bin/console doctrine:fixtures:load
  ```

6. **Start the server:**
    ```bash
    symfony server:start
    ```
    Or:
    ```bash
    php -S localhost:8000 -t public
    ```

## Testing the API

### Endpoint

- **POST /recommendations**
    - **Request Body:**
        ```json
        {
          "weather": {
            "city": "Paris",
          },
        "date": "today"
        }
        ```
    - **Response:**
        ```json
        {
          "products": [
            { "id": "1", "name": "T-shirt bleu", "price": 20.00 },
            { "id": "2", "name": "T-shirt rouge", "price": 20.00 }
          ],
          "weather": {
            "city": "Paris",
            "is": "hot",
            "date": "today"
          }
        }
        ```

You can test the API endpoint using tools like `curl` or Postman.

**Using `curl`:**
```bash
curl -X POST http://localhost:8000/recommendations \
     -H "Content-Type: application/json" \
     -d '{"weather": {"city": "Paris"},"date": "today"}'

