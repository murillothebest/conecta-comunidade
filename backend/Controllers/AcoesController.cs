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
public class AcoesController : ControllerBase
{
    private static readonly string[] StatusValidos =
        ["Planejada", "Em andamento", "Concluída", "Cancelada"];

    private readonly AppDbContext _db;

    public AcoesController(AppDbContext db) => _db = db;

    [HttpGet]
    public async Task<ActionResult<IEnumerable<AcaoDto>>> Listar()
    {
        var userId = User.GetUserId();

        var acoes = await _db.Acoes
            .Include(a => a.Categoria)
            .Include(a => a.Usuario)
            .Include(a => a.Inscricoes)
            .OrderBy(a => a.Data)
            .ThenByDescending(a => a.IdAcao)
            .ToListAsync();

        var result = acoes.Select(a => MapDto(a, userId));
        return Ok(result);
    }

    [HttpGet("{id:int}")]
    public async Task<ActionResult<AcaoDetalheDto>> Obter(int id)
    {
        var userId = User.GetUserId();
        var a = await _db.Acoes
            .Include(x => x.Categoria)
            .Include(x => x.Usuario)
            .Include(x => x.Inscricoes).ThenInclude(i => i.Usuario)
            .FirstOrDefaultAsync(x => x.IdAcao == id);

        if (a is null) return NotFound(new MensagemDto("Ação não encontrada."));

        var participantes = a.Inscricoes
            .OrderByDescending(i => i.DataInscricao)
            .Select(i => new ParticipanteDto(
                i.Usuario!.Nome,
                i.Usuario.Email,
                i.DataInscricao))
            .ToList();

        return Ok(new AcaoDetalheDto(
            a.IdAcao,
            a.Titulo,
            a.Descricao,
            a.Data.ToString("yyyy-MM-dd"),
            a.Local,
            a.Status,
            a.IdCategoria,
            a.Categoria?.Nome ?? "",
            a.IdUsuario,
            a.Usuario?.Nome ?? "",
            a.Usuario?.Email ?? "",
            participantes.Count,
            a.Inscricoes.Any(i => i.IdUsuario == userId),
            a.IdUsuario == userId,
            participantes
        ));
    }

    [HttpPost]
    public async Task<ActionResult<object>> Cadastrar([FromBody] AcaoCreateRequest req)
    {
        var erro = Validar(req.Titulo, req.Descricao, req.Data, req.Local, req.Status, req.IdCategoria);
        if (erro is not null) return BadRequest(new MensagemDto(erro));

        if (!await _db.Categorias.AnyAsync(c => c.IdCategoria == req.IdCategoria))
            return BadRequest(new MensagemDto("Categoria inválida."));

        var acao = new Acao
        {
            Titulo = req.Titulo.Trim(),
            Descricao = req.Descricao.Trim(),
            Data = DateOnly.Parse(req.Data),
            Local = req.Local.Trim(),
            Status = req.Status,
            IdCategoria = req.IdCategoria,
            IdUsuario = User.GetUserId()
        };

        _db.Acoes.Add(acao);
        await _db.SaveChangesAsync();

        return Ok(new { mensagem = "Ação cadastrada com sucesso.", idAcao = acao.IdAcao });
    }

    [HttpPut("{id:int}")]
    public async Task<ActionResult<MensagemDto>> Editar(int id, [FromBody] AcaoUpdateRequest req)
    {
        var acao = await _db.Acoes.FindAsync(id);
        if (acao is null) return NotFound(new MensagemDto("Ação não encontrada."));
        if (acao.IdUsuario != User.GetUserId())
            return StatusCode(StatusCodes.Status403Forbidden,
                new MensagemDto("Você só pode alterar ações criadas por você."));

        var erro = Validar(req.Titulo, req.Descricao, req.Data, req.Local, req.Status, req.IdCategoria);
        if (erro is not null) return BadRequest(new MensagemDto(erro));

        acao.Titulo = req.Titulo.Trim();
        acao.Descricao = req.Descricao.Trim();
        acao.Data = DateOnly.Parse(req.Data);
        acao.Local = req.Local.Trim();
        acao.Status = req.Status;
        acao.IdCategoria = req.IdCategoria;

        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Ação alterada com sucesso."));
    }

    [HttpDelete("{id:int}")]
    public async Task<ActionResult<MensagemDto>> Excluir(int id)
    {
        var acao = await _db.Acoes.FindAsync(id);
        if (acao is null) return NotFound(new MensagemDto("Ação não encontrada."));
        if (acao.IdUsuario != User.GetUserId())
            return StatusCode(StatusCodes.Status403Forbidden,
                new MensagemDto("Você só pode excluir ações criadas por você."));

        _db.Acoes.Remove(acao);
        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Ação excluída com sucesso."));
    }

    /// <summary>Participar da ação (inscrição)</summary>
    [HttpPost("{id:int}/participar")]
    public async Task<ActionResult<MensagemDto>> Participar(int id)
    {
        var userId = User.GetUserId();
        var acao = await _db.Acoes.FindAsync(id);
        if (acao is null) return NotFound(new MensagemDto("Ação não encontrada."));

        if (await _db.Inscricoes.AnyAsync(i => i.IdUsuario == userId && i.IdAcao == id))
            return Conflict(new MensagemDto("Você já está inscrito nesta ação."));

        _db.Inscricoes.Add(new Inscricao
        {
            IdUsuario = userId,
            IdAcao = id,
            DataInscricao = DateTime.UtcNow
        });

        try
        {
            await _db.SaveChangesAsync();
        }
        catch (DbUpdateException)
        {
            return Conflict(new MensagemDto("Você já está inscrito nesta ação."));
        }

        return Ok(new MensagemDto("Inscrição realizada com sucesso!"));
    }

    /// <summary>Cancelar participação</summary>
    [HttpDelete("{id:int}/participar")]
    public async Task<ActionResult<MensagemDto>> CancelarParticipacao(int id)
    {
        var userId = User.GetUserId();
        var insc = await _db.Inscricoes
            .FirstOrDefaultAsync(i => i.IdUsuario == userId && i.IdAcao == id);

        if (insc is null)
            return BadRequest(new MensagemDto("Você não possui inscrição nesta ação."));

        _db.Inscricoes.Remove(insc);
        await _db.SaveChangesAsync();
        return Ok(new MensagemDto("Participação cancelada com sucesso."));
    }

    private static string? Validar(string titulo, string descricao, string data, string local, string status, int idCategoria)
    {
        if (string.IsNullOrWhiteSpace(titulo) || titulo.Trim().Length < 3)
            return "Informe um título com pelo menos 3 caracteres.";
        if (string.IsNullOrWhiteSpace(descricao))
            return "Informe a descrição da ação.";
        if (!DateOnly.TryParse(data, out _))
            return "Informe uma data válida.";
        if (string.IsNullOrWhiteSpace(local))
            return "Informe o local da ação.";
        if (!StatusValidos.Contains(status))
            return "Status inválido.";
        if (idCategoria <= 0)
            return "Selecione uma categoria.";
        return null;
    }

    private static AcaoDto MapDto(Acao a, int userId) => new(
        a.IdAcao,
        a.Titulo,
        a.Descricao,
        a.Data.ToString("yyyy-MM-dd"),
        a.Local,
        a.Status,
        a.IdCategoria,
        a.Categoria?.Nome ?? "",
        a.IdUsuario,
        a.Usuario?.Nome ?? "",
        a.Inscricoes.Count,
        a.Inscricoes.Any(i => i.IdUsuario == userId),
        a.IdUsuario == userId
    );
}
