# Frendi (MVP мобильной веб-версии)

Проект для тестирования ключевых механик: лента, маршруты/квесты, фото животных, «Моя собака в ленте», конкурсы. Стек: Laravel + мобильный веб-интерфейс.

## Что реализовано в коде

- Лента с 4 типами постов: `route`, `pet`, `my_dog`, `contest`.
- Создание/редактирование/удаление постов (автор или админ), загрузка изображений.
- Лайки/дизлайки, комментарии, жалобы.
- Шаринг поста с публичной ссылкой `/share/{slug}` + OG-страницы.
- Модерация постов (pending/approved/rejected), автор видит свои посты до модерации.
- Админка: список постов, модерация, жалобы, метрики, выбор победителя конкурса.
- Блок «прошлый конкурс» в ленте (победитель, бейдж, рамка).
- Бесконечная лента с циклической подгрузкой и фильтром дублей.
- Обрезка длинного текста с кнопкой «ver».
- Интерфейс на испанском.

## Требования из ТЗ (собрано из PDF/DOCX)

- Лента с типами постов: маршруты, фото питомцев, «Моя собака», конкурсы (новые/прошедшие).
- Открытие комментариев в отдельном окне.
- Редактирование/удаление по меню «три точки».
- Генерация уникальной ссылки для «Поделиться».
- Модерация постов; без обязательной регистрации пользователей.
- Метрики: маршруты, фото животных, «Моя собака», конкурсы.
- Блок «прошлый конкурс» с победителем, бейджем и рамкой.
- Бесконечный скролл с зацикливанием.
- Лайтбокс/оверлей просмотра поста.
- «Показать ещё» для длинных текстов (ES: `Ver más`).
- Анимация лайка «лапкой».
- Поддержка испанского языка.
- Интеграции аналитики: Яндекс.Метрика (webvisor) и Google Analytics.

## Запуск (Docker)

Минимально:

```
docker compose up -d db app web
docker compose exec -T app composer install --no-interaction --prefer-dist
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan storage:link
```

Есть скрипт `init.sh` для первичной инициализации. Он настраивает `.env`, но использует `DB_CONNECTION=mysql`. Для текущего `docker-compose.yml` нужен `DB_CONNECTION=pgsql` и `DB_PORT=5432`.

Открыть: `http://127.0.0.1:8010`

## Переменные окружения (минимум)

- `APP_KEY`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `ADMIN_TOKEN` (для API-модерации)

## Админка

- Веб-вход: `/admin/login`
- Дефолтный админ сидится в миграции:
  - email: `77777green77777@gmail.com`
  - password: `DjdsdjJkjdlSasfd234356`

## API (основное)

База: `/api`

- `GET /feed` — лента (параметры: `per_page`, `types`).
- `POST /posts` — создать пост.
- `PATCH /posts/{id}` — обновить пост.
- `DELETE /posts/{id}` — удалить пост.
- `GET /posts/{id}` — получить пост.
- `GET /posts/{id}/comments` — комментарии.
- `POST /posts/{id}/comments` — добавить комментарий.
- `POST /posts/{id}/reactions` — like/dislike.
- `POST /posts/{id}/complaints` — жалоба.
- `POST /posts/{id}/share` — получить ссылку.
- `GET /share/{slug}` — данные поста по ссылке.

Админ API (требуется `X-Admin-Token` или `admin_token`):

- `GET /api/admin/posts`
- `PATCH /api/admin/posts/{id}/status`
- `GET /api/admin/complaints`
- `PATCH /api/admin/complaints/{id}/status`

## Клиентские идентификаторы

Для гостевых пользователей используются токен и отпечаток устройства:

- `X-Client-Token` или cookie `frendi_token`
- `X-Device-Fingerprint` или cookie `frendi_fingerprint`

