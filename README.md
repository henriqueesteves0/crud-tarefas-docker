# CRUD de Tarefas com PHP, MySQL e Docker Compose

## 1. Descrição do Projeto

Este projeto é uma aplicação web simples de **CRUD (Create, Read, Update, Delete)** desenvolvida em **PHP**, com persistência de dados em **MySQL**, totalmente containerizada com **Docker Compose**.

A entidade escolhida foi **Tarefa**, contendo os seguintes campos:

| Campo           | Tipo         | Descrição                              |
|-----------------|--------------|-----------------------------------------|
| `id`            | INT (PK, AI) | Identificador único, gerado automaticamente |
| `titulo`        | VARCHAR(150) | Título da tarefa                       |
| `descricao`     | TEXT         | Descrição detalhada da tarefa          |
| `data_cadastro` | DATETIME     | Data e hora em que a tarefa foi criada |

A aplicação permite:
- Listar todas as tarefas cadastradas.
- Cadastrar uma nova tarefa (formulário via POST).
- Editar uma tarefa existente (formulário pré-carregado).
- Excluir uma tarefa (com confirmação via JavaScript).

## 2. Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) instalado.
- [Docker Compose](https://docs.docker.com/compose/install/) instalado (já incluso no Docker Desktop e nas versões recentes do Docker Engine, via `docker compose`).

Não é necessário ter PHP ou MySQL instalados na máquina — tudo roda dentro dos containers.

## 3. Passo a Passo para Executar o Projeto

### 3.1. Clonar o repositório

```bash
git clone https://github.com/SEU_USUARIO/NOME_DO_REPOSITORIO.git
cd NOME_DO_REPOSITORIO
```

### 3.2. Subir os containers

```bash
docker-compose up -d
```

Esse comando irá:
1. Baixar as imagens do PHP (`php:8.2-apache`) e do MySQL (`mysql:8.0`), caso ainda não existam localmente.
2. Criar e iniciar os containers `crud-app` e `crud-db`.
3. Criar a rede `rede-crud` e o volume `dados-mysql`.

### 3.3. Criação da tabela no banco de dados

A tabela `tarefas` **é criada automaticamente pelo código PHP**. O arquivo `app/db.php` executa, a cada requisição, o comando:

```sql
CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    data_cadastro DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Ou seja, não é necessário rodar nenhum script SQL manualmente: assim que a aplicação recebe a primeira requisição, ela verifica se a tabela existe e a cria, caso necessário.

> Observação: como o container do MySQL pode demorar alguns segundos a mais para iniciar do que o container do PHP, o `db.php` também implementa uma pequena rotina de tentativas (retry) para aguardar o banco ficar disponível antes de travar a aplicação com erro de conexão.

### 3.4. Acessar a aplicação

Abra o navegador em:

```
http://localhost:8080
```

## 4. Explicação Detalhada do `docker-compose.yml`

O arquivo `docker-compose.yml` já contém comentários linha a linha explicando cada diretiva. Em resumo:

### Serviço `app`
- Usa a imagem pronta `php:8.2-apache`, que já vem com o Apache configurado para servir arquivos PHP.
- Mapeia a porta `8080` do host para a porta `80` do container (onde o Apache escuta).
- Monta a pasta local `./app` dentro de `/var/www/html` do container, para que qualquer alteração no código PHP seja refletida imediatamente, sem precisar reconstruir a imagem.
- Depende do serviço `db` (`depends_on`), garantindo uma ordem de inicialização mais adequada.
- Está conectado à rede `rede-crud`.

### Serviço `db`
- Usa a imagem oficial `mysql:8.0`.
- Usa `restart: always` para reiniciar automaticamente em caso de falha.
- Expõe a porta `3306` para permitir inspeção externa do banco (opcional, mas útil durante o desenvolvimento).
- Usa um volume nomeado (`dados-mysql`) para persistir os dados do banco, garantindo que as informações não sejam perdidas ao recriar os containers.
- Também está conectado à rede `rede-crud`.

### Variáveis de ambiente

Definidas diretamente na seção `environment` de cada serviço (sem uso de arquivo `.env`, conforme exigido):

| Variável              | Onde é usada | Função                                          |
|-----------------------|--------------|--------------------------------------------------|
| `DB_HOST`             | app          | Nome do host do banco (resolvido pela rede interna do Docker, usando o nome do serviço `db`) |
| `DB_USER`             | app          | Usuário do MySQL usado pela aplicação PHP        |
| `DB_PASSWORD`         | app          | Senha do usuário do MySQL                        |
| `DB_NAME`             | app          | Nome do banco de dados que a aplicação utiliza   |
| `MYSQL_ROOT_PASSWORD` | db           | Senha do usuário root, definida na criação do container MySQL |
| `MYSQL_DATABASE`      | db           | Nome do banco criado automaticamente na primeira inicialização do MySQL |

### Rede

Foi criada uma rede personalizada do tipo `bridge` chamada `rede-crud`. Ela permite que o container `app` se comunique com o container `db` usando apenas o **nome do serviço** (`db`) como se fosse um hostname, sem precisar descobrir o IP interno do container manualmente. Essa é a forma recomendada de comunicação entre containers no Docker Compose.

## 5. Pontos Interessantes Observados pela Dupla

1. Ao usar as variáveis de ambiente diretamente na seção `environment` do `docker-compose.yml`, conseguimos alterar credenciais e configurações do banco sem precisar tocar em nenhuma linha do código PHP — bastou reiniciar os containers.
2. O uso de um volume nomeado (`dados-mysql`) foi essencial para garantir a persistência dos dados: ao derrubar os containers com `docker-compose down` e subir novamente com `docker-compose up -d`, as tarefas cadastradas continuaram no banco.
3. Criar uma rede `bridge` personalizada (em vez de usar a rede padrão do Docker) deixou explícito quais containers deveriam se comunicar entre si, além de permitir que o Docker resolvesse o nome do serviço `db` automaticamente como se fosse um DNS interno.
4. Optar pela imagem `php:8.2-apache` pronta simplificou bastante o setup, pois ela já vem com o Apache configurado para servir arquivos `.php` sem nenhuma configuração adicional de `Dockerfile`.
5. Implementar uma pequena lógica de "retry" na conexão com o banco (em `db.php`) evitou erros de conexão causados pela diferença no tempo de inicialização entre o container do PHP e o container do MySQL.

## 6. Estrutura de Pastas

```
.
├── app/
│   ├── db.php        # Conexão com o banco e criação automática da tabela
│   ├── index.php      # Listagem de tarefas
│   ├── create.php     # Formulário de cadastro
│   ├── edit.php        # Formulário de edição
│   └── delete.php      # Exclusão de registros
├── docker-compose.yml   # Orquestração dos containers (app + db)
└── README.md
```

## 7. Autor

- Henrique Curioni Esteves/RA:240016