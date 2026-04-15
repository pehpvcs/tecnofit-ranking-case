# Tecnofit – Ranking de Movimento

API RESTful em PHP puro que retorna o ranking de usuários para um determinado movimento, com suporte a empates e busca por ID ou nome.

---

## Como rodar

### Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/install/) instalados.

```bash
git clone <url-do-repositorio>
cd tecnofit-ranking
docker compose up --build
```

O banco é criado e populado automaticamente. API disponível em `http://localhost:8080`.

---

## Endpoint

### `GET /movements/{identifier}/ranking`

`{identifier}` aceita o **ID numérico** ou o **nome** do movimento.

```bash
curl http://localhost:8080/movements/1/ranking
curl http://localhost:8080/movements/Deadlift/ranking
curl http://localhost:8080/movements/Back%20Squat/ranking
```

#### Resposta de sucesso (`200 OK`)

```json
{
  "movement": "Deadlift",
  "ranking": [
    { "name": "Jose",  "personal_record": 190, "rank": 1, "record_date": "2021-01-06 00:00:00" },
    { "name": "Joao",  "personal_record": 180, "rank": 2, "record_date": "2021-01-02 00:00:00" },
    { "name": "Paulo", "personal_record": 170, "rank": 3, "record_date": "2021-01-01 00:00:00" }
  ]
}
```

#### Resposta com empate – Back Squat (`200 OK`)

```json
{
  "movement": "Back Squat",
  "ranking": [
    { "name": "Joao",  "personal_record": 130, "rank": 1, "record_date": "2021-01-03 00:00:00" },
    { "name": "Jose",  "personal_record": 130, "rank": 1, "record_date": "2021-01-03 00:00:00" },
    { "name": "Paulo", "personal_record": 125, "rank": 2, "record_date": "2021-01-03 00:00:00" }
  ]
}
```

> Joao e Jose dividem a posição 1. Paulo fica em **2** (não em 3) graças ao `DENSE_RANK()`.

#### Códigos de erro

| Status | Situação |
|--------|----------|
| `404`  | Movimento não encontrado |
| `400`  | Identificador vazio |
| `500`  | Erro interno |

---

## Estrutura

```
tecnofit-ranking/
├── public/
│   └── index.php                   # Front Controller + roteamento
├── src/
│   ├── bootstrap.php               # Autoloader PSR-4 + .env + config de erros
│   ├── Database/
│   │   └── Connection.php          # Conexão PDO (Singleton)
│   ├── Http/
│   │   ├── Request.php             # Encapsulamento da requisição HTTP
│   │   └── Response.php            # Respostas JSON padronizadas
│   ├── Repository/
│   │   └── MovementRepository.php  # Queries SQL (Repository Pattern)
│   ├── Service/
│   │   └── RankingService.php      # Regras de negócio
│   └── Controller/
│       └── MovementController.php  # Camada HTTP
├── database/
│   └── schema.sql                  # DDL + seed
├── Dockerfile
├── docker-compose.yml
└── .env.example
```

---

## Decisões técnicas

### Arquitetura em camadas

Separação clara por responsabilidade (SRP do SOLID):

| Camada | Responsabilidade |
|--------|-----------------|
| **Controller** | Entrada/saída HTTP — recebe parâmetros, devolve JSON |
| **Service** | Regras de negócio — valida, orquestra, formata |
| **Repository** | Acesso a dados — SQL isolado aqui, zero lógica de negócio |

Essa separação torna cada camada testável isoladamente: o Service pode ser testado com um Repository fake sem tocar no banco.

### `DENSE_RANK()` para empates

A função `DENSE_RANK()` é parte do padrão **ANSI SQL:2003** e está disponível em MySQL 8, PostgreSQL, SQL Server e Oracle — portável sem alteração.

| Função | Resultado (130, 130, 125) | Problema |
|--------|--------------------------|----------|
| `ROW_NUMBER()` | 1, 2, 3 | Nunca empata |
| `RANK()` | 1, 1, **3** | Pula posições |
| `DENSE_RANK()` | 1, 1, **2** | Correto |

### CTEs para SQL legível

A query de ranking usa Common Table Expressions (`WITH`) para dividir a lógica em etapas nomeadas. O resultado é um SQL auto-documentado que o otimizador do MySQL trata da mesma forma que subqueries, sem overhead adicional.

### Prepared Statements em toda query

`PDO::prepare()` com parâmetros nomeados (`:movement_id`, `:name`) em todas as interações com o banco. Com `PDO::ATTR_EMULATE_PREPARES => false`, os prepared statements são executados no servidor MySQL — não emulados no PHP — o que oferece a máxima proteção contra SQL Injection.

### `MIN(date)` para a data do recorde

Quando um usuário bate o mesmo valor máximo em datas diferentes, retornamos a **mais antiga** — a primeira conquista do recorde, que é o dado semanticamente correto.

### Docker com healthcheck real

O serviço `app` só inicia após o healthcheck do MySQL (`mysqladmin ping`) passar, não apenas após o processo subir. Isso resolve a race condition clássica em ambientes Docker com banco de dados.

---

## Rodando sem Docker

```bash
# Requisitos: PHP 8.2+ com pdo_mysql, MySQL 8

mysql -u root -p < database/schema.sql
cp .env.example .env
php -S localhost:8080 -t public public/index.php
```
