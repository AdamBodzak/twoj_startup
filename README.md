Aplikacja REST API zbudowana w Laravel 12 umożliwiająca:

- CRUD użytkownika
- zarządzanie wieloma adresami e-mail przypisanymi do użytkownika
- wysyłkę przykładowej wiadomości e-mail:
  > "Witamy użytkownika XXX"

Projekt uruchamiany w środowisku Docker.
---

# Stack technologiczny

- PHP 8.4 (FPM)
- Laravel 12
- MySQL 9
- Nginx
- Docker / Docker Compose
- Mailpit (dev mail catcher)

---

# Uruchomienie Dockera

docker compose up -d --build

Aktualizacja pliku .env (plik .env.example zawiera te same dane co poniżej)

- DB_CONNECTION=mysql
- DB_HOST=twoj_startup-db
- DB_PORT=3306
- DB_DATABASE=twoj_startup
- DB_USERNAME=root
- DB_PASSWORD=root


- MAIL_MAILER=smtp
- MAIL_HOST=twoj_startup-mailpit
- MAIL_PORT=1025
- MAIL_USERNAME=null
- MAIL_PASSWORD=null
- MAIL_ENCRYPTION=null
- MAIL_FROM_ADDRESS="noreply@twoj-startup.local"
- MAIL_FROM_NAME="Twoj Startup"

composer install

php artisan migrate

dodatkowo trzeba dodać host w przypadku mac to należy zedytować plik /etc/hosts dodając 

> 127.0.0.1 twoj_startup.loc.pl

od teraz z aplikacją będzie można rozmawiać za pomocą domeny http://twoj_startup.loc.pl:8088/

e-mail jest wysyłany do kontenera aplikacji Mailpit. Podgląd jest możliwy w przeglądarce pod adresem http://localhost:8025/

Wejście do kontenera PHP
>docker exec -it recruitment-task-twoj_startup-php-1 sh

# Endpoints 

Do repo dodałem plik postman z dodanymi wszystkimi endpointami. Plik do importu do postman znajduje się w katalogu

> postman/Twój startap.postman_collection.json

Aplikacja zawiera następujące endpointy do obsługi użytkowników:

- GET /api/users -> Lista użytkowników
- GET /api/users/{id} -> Szczegóły użytkownika
- POST /api/users -> Tworzenie użytkownika
> Podczas tworzenia użytkownika wymagana jest następująca składnia dodawana do body w postaci json.
{
"first_name": "Adam",
"last_name": "Bodzak",
"phone": "+48500111222",
"login": "adamb",
"password": "password"
}

- PATCH /api/users/{id} -> Aktualizacja użytkownika
- DELETE /api/users/{id} -> Usunięcie użytkownika

Aplikacja zawiera następujące endpointy do obsługi emial:

- POST /api/users/{user_id}/emails -> Dodanie emaila

> Podczas email do użytkownika wymagana jest następująca składnia dodawana do body w postaci json.
{
"email": "test@example.com",
"is_primary": true
}

- PATCH /api/users/{user_id}/emails/{email_id} -> Aktualizacja emaila
- DELETE /api/users/{user_id}/emails/{email_id} -> Usunięcie emaila

Aplikacja umożliwia wysyłkę wiadomości email za pomocą url

- POST /api/users/{id}/send-welcome

Dodatkowo aplikacja posiada test wysyłki email który można wywołać

- php artisan test --filter=SendWelcomeMailTest

# Architektura

Aplikacja posiada kontrolery które są odpowiedzialne za obsługę żądań. Wszystkie żadania są walidowane z wykorzystaniem DI FormRequest.
Za wysyłkę email, logikę jest odpowiedzialna klasa SendWelcomeEmailToUser.
Dodatkowo w przypadku obsługi akcji na wielu modelach podczas np. dodawania użytkownika i email zastosowałem transakcje bazodanowe. 
Dodatkowo wykonując operacje na email należących do użytkowników zastosowałem sprawdzenie, czy użytkownik jest właścicielem danego e-mail. 

# Założenia projektowe

- Każdy użytkownik może mieć wiele adresów e-mail 
- Jeden email może być oznaczony jako primary 
- Usunięcie primary email powoduje automatyczne przypisanie nowego
- Mail wysyłany synchronicznie (dla uproszczenia zadania)
