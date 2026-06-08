# Sistema de Gestão Plalum

Sistema desenvolvido para a Unidade Curricular de Análise de Sistemas, utilizando a arquitetura Boundary-Control-Entity (BCE).

## Tecnologias Utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Composer

## Estrutura do Projeto

```text
PLALUM-FABRICA-DE-PLACAS/
├── backend/
    ├── src/
        ├── Boundary/
        ├── Control/
        ├── Entity/
    ├── vendor/
        ├── composer/
    ├── composer.json
    ├── index.php
├── database/
├── frontend/
└── README.md
```

# Funcionalidades

Cadastro de clientes
Cadastro de produtos
Controle de pedidos
Gestão de produção
Relatórios gerenciais

# Licença

MIT License

---

# Pré-requisitos

Antes de iniciar a instalação, certifique-se de possuir as seguintes ferramentas configuradas no seu ambiente de desenvolvimento:

## PHP

Instale o PHP a partir do site oficial e garanta que o executável esteja configurado nas variáveis de ambiente do sistema.

## Composer

Após instalar o PHP, instale o Composer para gerenciar as dependências e o autoload das classes do projeto.

## MySQL

Configure o servidor MySQL de forma independente.

> **Importante:** o serviço deve estar em execução antes de iniciar a aplicação.

## Cliente MySQL

Utilize qualquer cliente de sua preferência para administrar o banco de dados, por exemplo:

* MySQL Workbench
* DBeaver
* Beekeeper Studio
* HeidiSQL
* phpMyAdmin
* etc...

---

# Implantação Local

Siga os passos abaixo para configurar e executar o sistema.

## 1. Clonar o Repositório

Clone o projeto para sua máquina local:

```bash
git clone [URL_DO_REPOSITORIO]
```
```
Lembre-se que você pode instalar no Windows o GitHub Desktop para gerenciar seus projetos.
```

---

## 2. Gerar o Autoload das Classes

Acesse a pasta raiz do projeto e execute:

```bash
composer dump-autoload
```

Este comando permite que o PHP mapeie corretamente os namespaces e as classes do sistema.

---

## 3. Configurar o Banco de Dados

1. Abra seu cliente MySQL.
2. Crie uma nova base de dados.
    * Importe o script SQL disponibilizado no repositório no diretório database/.
4. Configure as credenciais de acesso (usuário e senha) na classe de conexão do projeto.

---

## 4. Iniciar a API (Back-end)

Abra um terminal apontando para a pasta do back-end e execute:

```bash
php -S localhost:8000
```

> Mantenha este terminal em execução.

---

## 5. Iniciar a Interface (Front-end)

Abra uma nova janela de terminal apontando para a pasta do front-end e execute:

```bash
php -S localhost:8001
```

---

## 6. Acessar o Sistema

Com ambos os serviços em execução, abra seu navegador e acesse:

```text
http://localhost:8001
```

---

# Arquitetura

O projeto segue o padrão **BCE (Boundary-Control-Entity)**:

| Camada   | Responsabilidade                       |
| -------- | -------------------------------------- |
| Boundary | Interface e comunicação com o usuário  |
| Control  | Regras de negócio e fluxo da aplicação |
| Entity   | Representação e manipulação dos dados  |

Esta separação promove maior organização, reutilização de código e facilidade de manutenção.

---

# Sistema de Gestão Plalum

Este repositório contém o software de exemplo desenvolvido para a Unidade Curricular de **Análise de Sistemas**.

O projeto visa a modernização do sistema de gestão de uma fábrica de placas metálicas, substituindo o modelo manual atualmente utilizado.

A aplicação foi estruturada utilizando o padrão arquitetural **Boundary-Control-Entity (BCE)**, com foco em:

* Alta coesão
* Baixo acoplamento
* Organização arquitetural
* Facilidade de manutenção e evolução

Todo o código-fonte serve como uma excelente base de estudo e prática para programadores, combinando fundamentos da Engenharia de Software com a implementação de regras de negócio em um cenário real.


# Objetivo Acadêmico

Este projeto foi desenvolvido com finalidade educacional, servindo como referência para:

* Análise de Sistemas
* Modelagem de Software
* Arquitetura BCE
* Programação Orientada a Objetos
* Integração PHP + MySQL
* Desenvolvimento de aplicações web

---
