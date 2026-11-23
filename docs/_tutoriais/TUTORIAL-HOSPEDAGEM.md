# Tutorial de hospedagem — Conecta Comunidade

Infra recomendada (sem Azure):

**GitHub Pages (HTML/CSS/JS) + API C# no Render + PostgreSQL no Filess.io**

```text
GitHub Pages  →  Render (API C#)  →  Filess.io (PostgreSQL)
```

Por que **Render** no lugar do Azure?
- cadastro simples, sem cartão no plano free
- encaixa bem com API em Docker
- URL HTTPS pronta (`*.onrender.com`)
- bom para apresentação acadêmica

> Observação: no plano free o serviço “dorme” após ~15 min sem uso.  
> Na demo, abra a API 1 vez antes de gravar o vídeo (primeiro request pode demorar ~30–60s).

---

## Ordem recomendada

1. Criar banco no **Filess.io**
2. Publicar a API no **Render**
3. Subir o código no **GitHub** + **GitHub Pages**
4. Ligar `API_BASE` e CORS
5. Testar Cadastro → Login → Participar

---

## 0. Pré-requisitos

- Conta **GitHub**
- Conta **Render** → [https://render.com](https://render.com)
- Conta **Filess.io** → [https://filess.io](https://filess.io)
- (Opcional no PC) [.NET 8 SDK](https://dotnet.microsoft.com/download) para testar local

---

## 1. Banco PostgreSQL no Filess.io

1. Crie conta no Filess.io
2. Crie um banco **PostgreSQL**
3. Monte a connection string:

```text
Host=xxxx.filess.io;Port=5432;Database=seu_banco;Username=seu_user;Password=sua_senha;SSL Mode=Require;Trust Server Certificate=true
```

4. (Opcional) rode `sql/banco_postgres.sql`  
   Se não rodar, a API cria as tabelas na primeira subida.

---

## 2. API C# no Render (substitui o Azure)

### 2.1 Subir o repositório no GitHub

```bash
cd conecta-comunidade
git init
git add .
git commit -m "Conecta Comunidade - Pages + Render + Filess"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/conecta-comunidade.git
git push -u origin main
```

### 2.2 Criar Web Service no Render

1. Acesse [https://dashboard.render.com](https://dashboard.render.com)
2. **New** → **Web Service**
3. Conecte o repositório GitHub
4. Configure:
   - **Name:** `conecta-comunidade-api`
   - **Root Directory:** `backend`
   - **Runtime:** **Docker**
   - **Dockerfile Path:** `Dockerfile` (já está em `backend/`)
   - **Instance type:** Free
5. Em **Environment**, adicione:

| Key | Value |
|-----|--------|
| `ConnectionStrings__Default` | connection string do Filess.io |
| `Jwt__Key` | chave secreta longa (32+ chars) |
| `Jwt__Issuer` | `ConectaComunidade` |
| `Jwt__Audience` | `ConectaComunidadeApp` |
| `Cors__Origins__0` | `https://SEU_USUARIO.github.io` |
| `Cors__Origins__1` | `https://SEU_USUARIO.github.io/conecta-comunidade` |
| `ASPNETCORE_ENVIRONMENT` | `Production` |

6. Clique em **Create Web Service** e aguarde o deploy.

URL exemplo:

```text
https://conecta-comunidade-api.onrender.com
```

Teste:

```text
https://SUA-API.onrender.com/swagger
https://SUA-API.onrender.com/
```

---

## 3. Frontend no GitHub Pages

O workflow `.github/workflows/ci.yml` publica a pasta `frontend/`.

1. Repo → **Settings** → **Pages** → Source: **GitHub Actions**
2. Push na `main` (ou rode o workflow em Actions)
3. Site: `https://SEU_USUARIO.github.io/conecta-comunidade/`

### Ligar o frontend na API

Edite `frontend/js/config.js`:

```js
window.APP_CONFIG = {
  API_BASE: 'https://conecta-comunidade-api.onrender.com'
};
```

Faça commit/push.  
Atalho sem novo deploy: no console do navegador:

```js
localStorage.setItem('API_BASE', 'https://SUA-API.onrender.com');
location.reload();
```

---

## 4. Checklist da demo

- [ ] Swagger da API abre (pode demorar no “acordar”)
- [ ] Cadastro cria usuário
- [ ] Login funciona
- [ ] Cards das ações aparecem
- [ ] Participar atualiza contagem
- [ ] Criar / editar / excluir ação própria
- [ ] Logout

**Dica de vídeo:** abra a URL da API 1 minuto antes de gravar, para evitar cold start na gravação.

---

## 5. O que falar na apresentação (DevOps)

1. **GitHub** — código (`frontend` + `backend`)
2. **GitHub Actions** — CI + deploy do frontend no **GitHub Pages**
3. **Render** — hospeda a API C# (Docker)
4. **Filess.io** — PostgreSQL
5. Fluxo: navegador → Pages → API → banco

Mostre a aba Actions (verde) + site no ar + Swagger.

---

## Plano B (sem Docker): Somee.com

Se o Render travar, use [Somee free ASP.NET](https://somee.com/FreeAspNetHosting.aspx):

- hospedagem Windows gratuita com .NET 8
- publish via Visual Studio / FTP
- limites: 150 MB, ads no site, banco próprio MS SQL (você pode continuar no Filess.io via connection string se a rede permitir)

Para a maioria das demos acadêmicas, **Render + Filess.io** é o caminho mais limpo.

---

## Problemas comuns

| Problema | Solução |
|----------|---------|
| CORS no navegador | Coloque a URL exata do Pages em `Cors__Origins__0` no Render |
| API demora 1ª vez | Cold start do free tier — abra antes da demo |
| 401 | Faça login de novo; confira `Jwt__Key` |
| Erro de banco | Connection string / SSL do Filess.io |
| Frontend chama localhost | Atualize `API_BASE` em `config.js` |

---

## Resumo das URLs

| Camada | Onde | Exemplo |
|--------|------|---------|
| Frontend | GitHub Pages | `https://user.github.io/conecta-comunidade/` |
| API | **Render** | `https://conecta-api.onrender.com` |
| Banco | Filess.io | host PostgreSQL do painel |

Boa apresentação!
