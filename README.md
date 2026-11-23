# Conecta Comunidade

Sistema acadêmico para **organizar ações comunitárias** e permitir que a comunidade **participe** delas (mutirões, arrecadações, oficinas, eventos, ações ambientais).

## Arquitetura (hospedagem)

```text
                 CONecta Comunidade
                        │
                        ▼
             ┌──────────────────┐
             │   GitHub Pages   │  ← frontend estático
             │  HTML/CSS/JS     │
             └────────┬─────────┘
                      │ API (HTTPS + JWT)
                      ▼
             ┌──────────────────┐
             │     Render       │  ← API C# (.NET 8 / Docker)
             │  (alternativa ao │
             │      Azure)      │
             └────────┬─────────┘
                      │ SQL
                      ▼
             ┌──────────────────┐
             │    Filess.io     │  ← PostgreSQL
             └──────────────────┘
```

O **GitHub** também é o repositório do código:

```text
GitHub
 ├── frontend/          → GitHub Pages
 ├── backend/           → Render (API C#)
 ├── sql/               → script PostgreSQL
 ├── docs/              → tutoriais
 └── .github/workflows → CI (build + Pages)
```

> XAMPP/PHP ficou arquivado em `legado-php/` só como referência.  
> A versão oficial para apresentar e hospedar é esta (Pages + Render + Filess.io).
>
> **Por que não Azure?** Render é mais simples para aluno (free, Docker, HTTPS pronto).  
> Azure DevOps/`azure-pipelines.yml` ficou opcional.

---

## Funcionalidades

- Cadastro de conta (`password` com BCrypt no backend)
- Login com **JWT** (token no navegador)
- Painel com **cards** de ações
- CRUD de **usuários**
- CRUD de **ações** (só o dono edita/exclui)
- **Participar / Cancelar participação** (UNIQUE no banco)
- 3 ações seed de demonstração
- CI no GitHub Actions (build da API + deploy do frontend)

---

## Estrutura

```text
conecta-comunidade/
├── frontend/                 # GitHub Pages
│   ├── index.html
│   ├── login.html
│   ├── cadastro.html
│   ├── painel.html
│   ├── css/
│   ├── js/
│   └── pages/
├── backend/                  # API ASP.NET Core 8
│   ├── Controllers/
│   ├── Models/
│   ├── Data/
│   ├── DTOs/
│   ├── Services/
│   └── ConectaComunidade.Api.csproj
├── sql/banco_postgres.sql
├── docs/TUTORIAL-HOSPEDAGEM.md
├── azure-pipelines.yml
├── .github/workflows/ci.yml
└── README.md
```

---

## Como rodar localmente (dev)

### 1. Banco

- Opção A: PostgreSQL local  
- Opção B: criar banco no **Filess.io** e copiar a connection string

Ajuste `backend/appsettings.Development.json`.

### 2. API

Requisitos: [.NET 8 SDK](https://dotnet.microsoft.com/download)

```bash
cd backend
dotnet restore
dotnet run
```

Swagger: `http://localhost:5080/swagger`

### 3. Frontend

Abra a pasta `frontend` com Live Server (VS Code) ou:

```bash
npx --yes serve frontend -p 5500
```

Confira em `frontend/js/config.js` se `API_BASE` aponta para `http://localhost:5080`.

---

## Tutorial completo de hospedagem

Passo a passo detalhado (Filess.io → Azure → GitHub Pages → ligar CORS):

👉 **[docs/TUTORIAL-HOSPEDAGEM.md](docs/TUTORIAL-HOSPEDAGEM.md)**

---

## Fluxo de demonstração (vídeo)

1. Abrir o site no GitHub Pages  
2. **Cadastre-se**  
3. **Login**  
4. Ver cards das ações  
5. Abrir uma ação → **Participar**  
6. Ver mensagem e contagem de participantes  
7. **Criar** uma ação  
8. **Editar**  
9. **Excluir** (com confirmação)  
10. Logout  

---

## CI/CD

| Onde | O que faz |
|------|-----------|
| **GitHub Actions** | Valida frontend, faz build da API C#, publica `frontend/` no **GitHub Pages** |
| **Render** | Faz deploy automático da API quando há push (se o serviço estiver ligado ao repo) |

Não usamos Azure como hospedagem principal. O arquivo `azure-pipelines.yml` é só referência opcional.

---

## Segurança

- Senhas com **BCrypt**
- Autenticação **JWT**
- CORS liberado só para origens configuradas
- UNIQUE `(id_usuario, id_acao)` em inscrições
- Autorização: só o criador edita/exclui a ação
