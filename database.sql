DROP TABLE IF EXISTS urls;

CREATE TABLE urls (
    id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(255) unique NOT NULL,
    created_at timestamp
);

DROP TABLE IF EXISTS url_checks;

CREATE TABLE url_checks (
    id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    url_id bigint NOT NULL REFERENCES urls(id),
    status_code int,
    h1 VARCHAR,
    title VARCHAR,
    description VARCHAR,
    created_at timestamp
);