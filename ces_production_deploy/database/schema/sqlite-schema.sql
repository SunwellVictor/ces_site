CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "avatar_path" varchar
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "display_name" varchar not null,
  "description" text,
  "permissions" text,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "slug" varchar not null
);
CREATE UNIQUE INDEX "roles_name_unique" on "roles"("name");
CREATE TABLE IF NOT EXISTS "downloadable_files"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "filename" varchar not null,
  "file_path" varchar not null,
  "file_type" varchar not null,
  "file_size" integer not null,
  "description" text,
  "product_id" integer not null,
  "is_active" tinyint(1) not null default '1',
  "download_limit" integer,
  "download_count" integer not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "downloadable_files_product_id_is_active_index" on "downloadable_files"(
  "product_id",
  "is_active"
);
CREATE INDEX "downloadable_files_file_type_index" on "downloadable_files"(
  "file_type"
);
CREATE UNIQUE INDEX "roles_slug_unique" on "roles"("slug");
CREATE TABLE IF NOT EXISTS "role_user"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "role_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade
);
CREATE UNIQUE INDEX "role_user_user_id_role_id_unique" on "role_user"(
  "user_id",
  "role_id"
);
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "slug" varchar not null,
  "title" varchar not null,
  "description" text not null,
  "price_cents" integer not null,
  "currency" varchar not null default 'JPY',
  "is_active" tinyint(1) not null default '1',
  "is_digital" tinyint(1) not null default '1',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "products_is_active_is_digital_index" on "products"(
  "is_active",
  "is_digital"
);
CREATE INDEX "products_slug_index" on "products"("slug");
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
CREATE TABLE IF NOT EXISTS "files"(
  "id" integer primary key autoincrement not null,
  "disk" varchar not null default 'public',
  "path" varchar not null,
  "original_name" varchar not null,
  "size_bytes" integer not null,
  "checksum" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "files_disk_index" on "files"("disk");
CREATE INDEX "files_path_index" on "files"("path");
CREATE TABLE IF NOT EXISTS "product_files"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "file_id" integer not null,
  "note" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("file_id") references "files"("id") on delete cascade
);
CREATE INDEX "product_files_product_id_file_id_index" on "product_files"(
  "product_id",
  "file_id"
);
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "total_cents" integer not null,
  "currency" varchar not null default 'JPY',
  "status" varchar check("status" in('pending', 'paid', 'failed', 'refunded')) not null default 'pending',
  "stripe_session_id" varchar,
  "stripe_payment_intent" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "orders_user_id_status_index" on "orders"("user_id", "status");
CREATE INDEX "orders_stripe_session_id_index" on "orders"("stripe_session_id");
CREATE INDEX "orders_stripe_payment_intent_index" on "orders"(
  "stripe_payment_intent"
);
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer not null,
  "qty" integer not null default '1',
  "unit_price_cents" integer not null,
  "line_total_cents" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "order_items_order_id_product_id_index" on "order_items"(
  "order_id",
  "product_id"
);
CREATE TABLE IF NOT EXISTS "download_tokens"(
  "id" integer primary key autoincrement not null,
  "grant_id" integer not null,
  "token" varchar not null,
  "expires_at" datetime not null,
  "used_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("grant_id") references "download_grants"("id") on delete cascade
);
CREATE INDEX "download_tokens_token_expires_at_index" on "download_tokens"(
  "token",
  "expires_at"
);
CREATE INDEX "download_tokens_grant_id_used_at_index" on "download_tokens"(
  "grant_id",
  "used_at"
);
CREATE UNIQUE INDEX "download_tokens_token_unique" on "download_tokens"(
  "token"
);
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "slug" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "description" text,
  "status" varchar not null default 'active'
);
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "category_post"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "post_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("category_id") references "categories"("id") on delete cascade,
  foreign key("post_id") references "posts"("id") on delete cascade
);
CREATE UNIQUE INDEX "category_post_category_id_post_id_unique" on "category_post"(
  "category_id",
  "post_id"
);
CREATE TABLE IF NOT EXISTS "posts"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "slug" varchar not null,
  "excerpt" text,
  "featured_image" varchar,
  "status" varchar check("status" in('draft', 'published')) not null default 'draft',
  "author_id" integer not null,
  "published_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "body" text not null,
  "seo_title" varchar,
  "seo_description" varchar,
  foreign key("author_id") references users("id") on delete cascade on update no action
);
CREATE INDEX "posts_author_id_status_index" on "posts"("author_id", "status");
CREATE INDEX "posts_published_at_index" on "posts"("published_at");
CREATE UNIQUE INDEX "posts_slug_unique" on "posts"("slug");
CREATE TABLE IF NOT EXISTS "download_grants"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "product_id" integer not null,
  "file_id" integer not null,
  "order_id" integer,
  "max_downloads" integer not null default('5'),
  "downloads_used" integer not null default('0'),
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("file_id") references files("id") on delete cascade on update no action,
  foreign key("product_id") references products("id") on delete cascade on update no action,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE INDEX "download_grants_expires_at_index" on "download_grants"(
  "expires_at"
);
CREATE INDEX "download_grants_order_id_user_id_index" on "download_grants"(
  "order_id",
  "user_id"
);
CREATE INDEX "download_grants_user_id_product_id_file_id_index" on "download_grants"(
  "user_id",
  "product_id",
  "file_id"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_09_26_001429_create_roles_table',1);
INSERT INTO migrations VALUES(5,'2025_09_26_001745_create_posts_table',1);
INSERT INTO migrations VALUES(6,'2025_09_26_002004_create_downloadable_files_table',1);
INSERT INTO migrations VALUES(7,'2025_09_26_002810_add_avatar_path_to_users_table',1);
INSERT INTO migrations VALUES(8,'2025_09_26_002838_add_slug_to_roles_table',1);
INSERT INTO migrations VALUES(9,'2025_09_26_002851_create_role_user_table',1);
INSERT INTO migrations VALUES(10,'2025_09_26_002916_create_products_table',1);
INSERT INTO migrations VALUES(11,'2025_09_26_002931_create_files_table',1);
INSERT INTO migrations VALUES(12,'2025_09_26_002946_create_product_files_table',1);
INSERT INTO migrations VALUES(13,'2025_09_26_002959_create_orders_table',1);
INSERT INTO migrations VALUES(14,'2025_09_26_003014_create_order_items_table',1);
INSERT INTO migrations VALUES(15,'2025_09_26_003029_create_download_grants_table',1);
INSERT INTO migrations VALUES(16,'2025_09_26_003044_create_download_tokens_table',1);
INSERT INTO migrations VALUES(17,'2025_09_26_003820_create_categories_table',1);
INSERT INTO migrations VALUES(18,'2025_09_26_003847_create_category_post_table',1);
INSERT INTO migrations VALUES(19,'2025_09_26_004013_update_posts_table',1);
INSERT INTO migrations VALUES(20,'2025_09_26_040344_add_description_and_status_to_categories_table',1);
INSERT INTO migrations VALUES(21,'2025_09_26_041546_make_order_id_nullable_in_download_grants_table',1);
