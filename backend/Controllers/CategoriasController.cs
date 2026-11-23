using ConectaComunidade.Api.Data;
using ConectaComunidade.Api.DTOs;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace ConectaComunidade.Api.Controllers;

[ApiController]
[Authorize]
[Route("api/[controller]")]
public class CategoriasController : ControllerBase
{
    private readonly AppDbContext _db;

    public CategoriasController(AppDbContext db) => _db = db;

    [HttpGet]
    public async Task<ActionResult<IEnumerable<CategoriaDto>>> Listar()
    {
        var lista = await _db.Categorias
            .OrderBy(c => c.Nome)
            .Select(c => new CategoriaDto(c.IdCategoria, c.Nome, c.Descricao))
            .ToListAsync();
        return Ok(lista);
    }
}
