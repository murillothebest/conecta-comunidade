using ConectaComunidade.Api.Data;
using ConectaComunidade.Api.DTOs;
using ConectaComunidade.Api.Models;
using ConectaComunidade.Api.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ConectaComunidade.Api.Controllers;

[ApiController]
[Authorize]
[Route("api/[controller]")]
public class UsuariosController : ControllerBase
{
    private readonly AppDbContext _db;

    public UsuariosController(AppDbContext db) => _db = db;

    [HttpGet]
    public async Task<ActionResult<IEnumerable<UsuarioDto>>> Listar()
    {
        var lista = await _db.Usuarios
            .OrderBy(u => u.Nome)
            .Select(u => new UsuarioDto(u.IdUsuario, u.Nome, u.Email, u.DataCadastro))
            .ToListAsync();
        return Ok(lista);
    }

    [HttpGet("{id:int}")]
    public async Task<ActionResult<UsuarioDto>> Obter(int id)
    {
        var u = await _db.Usuarios.FindAsync(id);
        if (u is null) return NotFound(new MensagemDto("Usuário não encontrado."));
        return Ok(new UsuarioDto(u.IdUsuario, u.Nome, u.Email, u.DataCadastro));
    }

    [HttpPost]
    public async Task<ActionResult<MensagemDto>> Cadastrar([FromBody] UsuarioCreateRequest req)
    {
        var email = req.Email.Trim().ToLowerInvariant();
        if (await _db.Usuarios.AnyAsync(u => u.Email == email))
            return Conflict(new MensagemDto("Já existe um usuário com este e-mail."));

        _db.Usuarios.Add(new Usuario
        {
            Nome = req.Nome.Trim(),
            Email = email,
            Senha = BCrypt.Net.BCrypt.HashPassword(req.Senha),
            DataCadastro = DateTime.UtcNow
        });
        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Usuário cadastrado com sucesso."));
    }

    [HttpPut("{id:int}")]
    public async Task<ActionResult<MensagemDto>> Editar(int id, [FromBody] UsuarioUpdateRequest req)
    {
        var u = await _db.Usuarios.FindAsync(id);
        if (u is null) return NotFound(new MensagemDto("Usuário não encontrado."));

        var email = req.Email.Trim().ToLowerInvariant();
        if (await _db.Usuarios.AnyAsync(x => x.Email == email && x.IdUsuario != id))
            return Conflict(new MensagemDto("Já existe outro usuário com este e-mail."));

        u.Nome = req.Nome.Trim();
        u.Email = email;
        if (!string.IsNullOrWhiteSpace(req.Senha))
        {
            if (req.Senha.Length < 6)
                return BadRequest(new MensagemDto("A nova senha deve ter pelo menos 6 caracteres."));
            u.Senha = BCrypt.Net.BCrypt.HashPassword(req.Senha);
        }

        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Usuário alterado com sucesso."));
    }

    [HttpDelete("{id:int}")]
    public async Task<ActionResult<MensagemDto>> Excluir(int id)
    {
        var userId = User.GetUserId();
        if (userId == id)
            return BadRequest(new MensagemDto("Você não pode excluir o usuário logado."));

        var u = await _db.Usuarios.FindAsync(id);
        if (u is null) return NotFound(new MensagemDto("Usuário não encontrado."));

        if (await _db.Acoes.AnyAsync(a => a.IdUsuario == id))
            return BadRequest(new MensagemDto("Não é possível excluir: este usuário possui ações vinculadas."));

        _db.Usuarios.Remove(u);
        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Usuário excluído com sucesso."));
    }
}
