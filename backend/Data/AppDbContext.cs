using ConectaComunidade.Api.Models;
using Microsoft.EntityFrameworkCore;

namespace ConectaComunidade.Api.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }

    public DbSet<Usuario> Usuarios => Set<Usuario>();
    public DbSet<Categoria> Categorias => Set<Categoria>();
    public DbSet<Acao> Acoes => Set<Acao>();
    public DbSet<Inscricao> Inscricoes => Set<Inscricao>();
    public DbSet<Comentario> Comentarios => Set<Comentario>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder.HasDefaultSchema("conecta");

        modelBuilder.Entity<Usuario>()
            .HasIndex(u => u.Email)
            .IsUnique();

        modelBuilder.Entity<Categoria>()
            .HasIndex(c => c.Nome)
            .IsUnique();

        modelBuilder.Entity<Inscricao>()
            .HasIndex(i => new { i.IdUsuario, i.IdAcao })
            .IsUnique();

        modelBuilder.Entity<Acao>()
            .HasOne(a => a.Categoria)
            .WithMany(c => c.Acoes)
            .HasForeignKey(a => a.IdCategoria)
            .OnDelete(DeleteBehavior.Restrict);

        modelBuilder.Entity<Acao>()
            .HasOne(a => a.Usuario)
            .WithMany(u => u.Acoes)
            .HasForeignKey(a => a.IdUsuario)
            .OnDelete(DeleteBehavior.Restrict);

        modelBuilder.Entity<Inscricao>()
            .HasOne(i => i.Usuario)
            .WithMany(u => u.Inscricoes)
            .HasForeignKey(i => i.IdUsuario)
            .OnDelete(DeleteBehavior.Cascade);

        modelBuilder.Entity<Inscricao>()
            .HasOne(i => i.Acao)
            .WithMany(a => a.Inscricoes)
            .HasForeignKey(i => i.IdAcao)
            .OnDelete(DeleteBehavior.Cascade);

        modelBuilder.Entity<Comentario>()
            .HasOne(c => c.Usuario)
            .WithMany(u => u.Comentarios)
            .HasForeignKey(c => c.IdUsuario)
            .OnDelete(DeleteBehavior.Cascade);

        modelBuilder.Entity<Comentario>()
            .HasOne(c => c.Acao)
            .WithMany(a => a.Comentarios)
            .HasForeignKey(c => c.IdAcao)
            .OnDelete(DeleteBehavior.Cascade);

        // Categorias seed (sempre úteis)
        modelBuilder.Entity<Categoria>().HasData(
            new Categoria { IdCategoria = 1, Nome = "Arrecadação", Descricao = "Campanhas de doação e arrecadação" },
            new Categoria { IdCategoria = 2, Nome = "Mutirão", Descricao = "Trabalhos coletivos voluntários" },
            new Categoria { IdCategoria = 3, Nome = "Reunião", Descricao = "Encontros da comunidade" },
            new Categoria { IdCategoria = 4, Nome = "Atividade Social", Descricao = "Convivência e apoio social" },
            new Categoria { IdCategoria = 5, Nome = "Evento Cultural", Descricao = "Feiras e manifestações culturais" },
            new Categoria { IdCategoria = 6, Nome = "Ação Ambiental", Descricao = "Limpeza e preservação" },
            new Categoria { IdCategoria = 7, Nome = "Educação", Descricao = "Oficinas e reforço escolar" }
        );
    }
}
