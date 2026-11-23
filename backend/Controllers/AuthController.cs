using ConectaComunidade.Api.Data;
using ConectaComunidade.Api.DTOs;
using ConectaComunidade.Api.Models;
using ConectaComunidade.Api.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ConectaComunidade.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AuthController : ControllerBase
{
    private readonly AppDbContext _db;
    private readonly JwtService _jwt;

    public AuthController(AppDbContext db, JwtService jwt)
    {
        _db = db;
        _jwt = jwt;
    }

    /// <summary>Cadastro público — equivalente ao cadastro.php</summary>
    [HttpPost("cadastro")]
    [AllowAnonymous]
    public async Task<ActionResult<MensagemDto>> Cadastro([FromBody] CadastroRequest req)
    {
        var email = req.Email.Trim().ToLowerInvariant();
        if (await _db.Usuarios.AnyAsync(u => u.Email == email))
            return Conflict(new MensagemDto("Já existe uma conta com este e-mail."));

        var usuario = new Usuario
        {
            Nome = req.Nome.Trim(),
            Email = email,
            Senha = BCrypt.Net.BCrypt.HashPassword(req.Senha),
            DataCadastro = DateTime.UtcNow
        };

        _db.Usuarios.Add(usuario);
        await _db.SaveChangesAsync();

        return Ok(new MensagemDto("Conta criada com sucesso! Faça login para continuar."));
    }

    /// <summary>Login — retorna JWT para o frontend no GitHub Pages</summary>
    [HttpPost("login")]
    [AllowAnonymous]
    public async Task<ActionResult<AuthResponse>> Login([FromBody] LoginRequest req)
    {
        var email = req.Email.Trim().ToLowerInvariant();
        var usuario = await _db.Usuarios.FirstOrDefaultAsync(u => u.Email == email);

        if (usuario is null || !BCrypt.Net.BCrypt.Verify(req.Senha, usuario.Senha))
            return Unauthorized(new MensagemDto("E-mail ou senha incorretos."));

        var token = _jwt.GerarToken(usuario);
        return Ok(new AuthResponse(token, usuario.IdUsuario, usuario.Nome, usuario.Email));
    }

    [HttpGet("me")]
    [Authorize]
    public async Task<ActionResult<UsuarioDto>> Me()
    {
        var id = User.GetUserId();
        var usuario = await _db.Usuarios.FindAsync(id);
        if (usuario is null) return Unauthorized();

        return Ok(new UsuarioDto(usuario.IdUsuario, usuario.Nome, usuario.Email, usuario.DataCadastro));
    }
}
