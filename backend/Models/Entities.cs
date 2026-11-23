using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace ConectaComunidade.Api.Models;

[Table("usuarios")]
public class Usuario
{
    [Key]
    [Column("id_usuario")]
    public int IdUsuario { get; set; }

    [Required, MaxLength(100)]
    [Column("nome")]
    public string Nome { get; set; } = string.Empty;

    [Required, MaxLength(150)]
    [Column("email")]
    public string Email { get; set; } = string.Empty;

    [Required, MaxLength(255)]
    [Column("senha")]
    public string Senha { get; set; } = string.Empty;

    [Column("data_cadastro")]
    public DateTime DataCadastro { get; set; } = DateTime.UtcNow;

    public ICollection<Acao> Acoes { get; set; } = new List<Acao>();
    public ICollection<Inscricao> Inscricoes { get; set; } = new List<Inscricao>();
    public ICollection<Comentario> Comentarios { get; set; } = new List<Comentario>();
}

[Table("categorias")]
public class Categoria
{
    [Key]
    [Column("id_categoria")]
    public int IdCategoria { get; set; }

    [Required, MaxLength(80)]
    [Column("nome")]
    public string Nome { get; set; } = string.Empty;

    [MaxLength(255)]
    [Column("descricao")]
    public string? Descricao { get; set; }

    public ICollection<Acao> Acoes { get; set; } = new List<Acao>();
}

[Table("acoes")]
public class Acao
{
    [Key]
    [Column("id_acao")]
    public int IdAcao { get; set; }

    [Required, MaxLength(150)]
    [Column("titulo")]
    public string Titulo { get; set; } = string.Empty;

    [Required]
    [Column("descricao")]
    public string Descricao { get; set; } = string.Empty;

    [Column("data")]
    public DateOnly Data { get; set; }

    [Required, MaxLength(150)]
    [Column("local")]
    public string Local { get; set; } = string.Empty;

    [Required, MaxLength(30)]
    [Column("status")]
    public string Status { get; set; } = "Planejada";

    [Column("id_categoria")]
    public int IdCategoria { get; set; }

    [Column("id_usuario")]
    public int IdUsuario { get; set; }

    public Categoria? Categoria { get; set; }
    public Usuario? Usuario { get; set; }
    public ICollection<Inscricao> Inscricoes { get; set; } = new List<Inscricao>();
    public ICollection<Comentario> Comentarios { get; set; } = new List<Comentario>();
}

[Table("inscricoes")]
public class Inscricao
{
    [Key]
    [Column("id_inscricao")]
    public int IdInscricao { get; set; }

    [Column("id_usuario")]
    public int IdUsuario { get; set; }

    [Column("id_acao")]
    public int IdAcao { get; set; }

    [Column("data_inscricao")]
    public DateTime DataInscricao { get; set; } = DateTime.UtcNow;

    public Usuario? Usuario { get; set; }
    public Acao? Acao { get; set; }
}

[Table("comentarios")]
public class Comentario
{
    [Key]
    [Column("id_comentario")]
    public int IdComentario { get; set; }

    [Column("id_usuario")]
    public int IdUsuario { get; set; }

    [Column("id_acao")]
    public int IdAcao { get; set; }

    [Required]
    [Column("comentario")]
    public string Texto { get; set; } = string.Empty;

    [Column("data_comentario")]
    public DateTime DataComentario { get; set; } = DateTime.UtcNow;

    public Usuario? Usuario { get; set; }
    public Acao? Acao { get; set; }
}
