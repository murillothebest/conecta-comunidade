using System.Text;
using ConectaComunidade.Api.Data;
using ConectaComunidade.Api.Models;
using ConectaComunidade.Api.Services;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using Microsoft.OpenApi.Models;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
{
    c.SwaggerDoc("v1", new OpenApiInfo
    {
        Title = "Conecta Comunidade API",
        Version = "v1",
        Description = "API C# do Conecta Comunidade (Render / Filess.io PostgreSQL)"
    });
    c.AddSecurityDefinition("Bearer", new OpenApiSecurityScheme
    {
        Description = "JWT: cole o token (sem a palavra Bearer se o UI adicionar).",
        Name = "Authorization",
        In = ParameterLocation.Header,
        Type = SecuritySchemeType.Http,
        Scheme = "bearer",
        BearerFormat = "JWT"
    });
    c.AddSecurityRequirement(new OpenApiSecurityRequirement
    {
        {
            new OpenApiSecurityScheme
            {
                Reference = new OpenApiReference { Type = ReferenceType.SecurityScheme, Id = "Bearer" }
            },
            Array.Empty<string>()
        }
    });
});

builder.Services.AddDbContext<AppDbContext>(options =>
    options.UseNpgsql(builder.Configuration.GetConnectionString("Default")));

builder.Services.AddScoped<JwtService>();

var jwtKey = builder.Configuration["Jwt:Key"] ?? "TroqueEstaChavePorUmaSecretaBemLonga123456!";
builder.Services
    .AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuer = true,
            ValidateAudience = true,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,
            ValidIssuer = builder.Configuration["Jwt:Issuer"],
            ValidAudience = builder.Configuration["Jwt:Audience"],
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey))
        };
    });

builder.Services.AddAuthorization();

var origins = builder.Configuration.GetSection("Cors:Origins").Get<string[]>()
              ?? ["http://localhost:5500"];

builder.Services.AddCors(options =>
{
    options.AddPolicy("Frontend", policy =>
        policy.WithOrigins(origins)
              .AllowAnyHeader()
              .AllowAnyMethod());
});

var app = builder.Build();

// Cria tabelas e seeds básicos na subida (simples para projeto acadêmico)
using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<AppDbContext>();
    var logger = scope.ServiceProvider.GetRequiredService<ILoggerFactory>().CreateLogger("Startup");
    try
    {
        // Filess.io: schema "public" costuma ser bloqueado — usamos "conecta"
        await db.Database.ExecuteSqlRawAsync(@"CREATE SCHEMA IF NOT EXISTS conecta;");
        await db.Database.ExecuteSqlRawAsync(@"SET search_path TO conecta;");
        db.Database.EnsureCreated();
        await SeedDemoAsync(db);
        logger.LogInformation("Banco pronto (schema conecta + seed).");
    }
    catch (Exception ex)
    {
        logger.LogError(ex, "Falha ao conectar/criar banco. Verifique ConnectionStrings__Default (SSL Mode).");
        throw;
    }
}

if (app.Environment.IsDevelopment() || app.Environment.IsProduction())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseCors("Frontend");
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();

app.MapGet("/", () => Results.Ok(new
{
    sistema = "Conecta Comunidade API",
    status = "ok",
    swagger = "/swagger"
}));

// Render (e similares) definem a variável PORT
var port = Environment.GetEnvironmentVariable("PORT");
if (!string.IsNullOrWhiteSpace(port))
{
    app.Urls.Add($"http://0.0.0.0:{port}");
}

app.Run();

static async Task SeedDemoAsync(AppDbContext db)
{
    // Usuário seed só para vincular ações demo — a apresentação usa Cadastre-se
    if (!await db.Usuarios.AnyAsync())
    {
        var seed = new Usuario
        {
            Nome = "Organizador Seed",
            Email = "seed@conecta.local",
            Senha = BCrypt.Net.BCrypt.HashPassword("seed-apenas-dev"),
            DataCadastro = DateTime.UtcNow
        };
        db.Usuarios.Add(seed);
        await db.SaveChangesAsync();

        if (!await db.Acoes.AnyAsync())
        {
            db.Acoes.AddRange(
                new Acao
                {
                    Titulo = "Mutirão de Limpeza da Praça",
                    Descricao = "Encontro coletivo para limpeza e revitalização da praça do bairro. Traga luvas e disposição.",
                    Data = new DateOnly(2026, 10, 5),
                    Local = "Praça Central",
                    Status = "Planejada",
                    IdCategoria = 6,
                    IdUsuario = seed.IdUsuario
                },
                new Acao
                {
                    Titulo = "Campanha de Arrecadação de Alimentos",
                    Descricao = "Campanha solidária de alimentos não perecíveis para famílias da comunidade.",
                    Data = new DateOnly(2026, 10, 12),
                    Local = "Salão Comunitário",
                    Status = "Em andamento",
                    IdCategoria = 1,
                    IdUsuario = seed.IdUsuario
                },
                new Acao
                {
                    Titulo = "Oficina de Reforço Escolar",
                    Descricao = "Oficina gratuita de reforço em matemática e português para o ensino fundamental.",
                    Data = new DateOnly(2026, 10, 20),
                    Local = "Centro Comunitário - Sala 2",
                    Status = "Planejada",
                    IdCategoria = 7,
                    IdUsuario = seed.IdUsuario
                }
            );
            await db.SaveChangesAsync();
        }
    }
}
