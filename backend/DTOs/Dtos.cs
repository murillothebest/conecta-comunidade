using System.ComponentModel.DataAnnotations;

namespace ConectaComunidade.Api.DTOs;

public record CadastroRequest(
    [Required, MinLength(3)] string Nome,
    [Required, EmailAddress] string Email,
    [Required, MinLength(6)] string Senha
);

public record LoginRequest(
    [Required, EmailAddress] string Email,
    [Required] string Senha
);

public record AuthResponse(string Token, int IdUsuario, string Nome, string Email);

public record UsuarioDto(int IdUsuario, string Nome, string Email, DateTime DataCadastro);

public record UsuarioCreateRequest(
    [Required, MinLength(3)] string Nome,
    [Required, EmailAddress] string Email,
    [Required, MinLength(6)] string Senha
);

public record UsuarioUpdateRequest(
    [Required, MinLength(3)] string Nome,
    [Required, EmailAddress] string Email,
    string? Senha
);

public record AcaoDto(
    int IdAcao,
    string Titulo,
    string Descricao,
    string Data,
    string Local,
    string Status,
    int IdCategoria,
    string Categoria,
    int IdUsuario,
    string Organizador,
    int TotalParticipantes,
    bool UsuarioInscrito,
    bool SouDono
);

public record AcaoCreateRequest(
    [Required, MinLength(3)] string Titulo,
    [Required] string Descricao,
    [Required] string Data,
    [Required] string Local,
    [Required] string Status,
    [Required] int IdCategoria
);

public record AcaoUpdateRequest(
    [Required, MinLength(3)] string Titulo,
    [Required] string Descricao,
    [Required] string Data,
    [Required] string Local,
    [Required] string Status,
    [Required] int IdCategoria
);

public record ParticipanteDto(string Nome, string Email, DateTime DataInscricao);

public record AcaoDetalheDto(
    int IdAcao,
    string Titulo,
    string Descricao,
    string Data,
    string Local,
    string Status,
    int IdCategoria,
    string Categoria,
    int IdUsuario,
    string Organizador,
    string EmailOrganizador,
    int TotalParticipantes,
    bool UsuarioInscrito,
    bool SouDono,
    IEnumerable<ParticipanteDto> Participantes
);

public record CategoriaDto(int IdCategoria, string Nome, string? Descricao);

public record MensagemDto(string Mensagem);
