#include "Includes/fractol.h"

int		outside_window(t_env *e, int x, int y)
{
	if (!(x > e->large - 1 || x < 0 || y > e->longer - 1 || y < 0))
		return (1);
	else
		return (0);
}

t_var	init_var(t_milieu p1, t_milieu p2)
{
	t_var	var;

	var.dx = abs((int)p2.x - (int)p1.x);
	var.sx = p1.x < p2.x ? 1 : -1;
	var.dy = abs((int)p2.y - (int)p1.y);
	var.sy = p1.y < p2.y ? 1 : -1;
	var.error = (var.dx > var.dy ? var.dx : -var.dy) / 2;
	var.x = p1.x;
	var.y = p1.y;
	return (var);
}

void	print_line(t_milieu a, t_milieu b, t_env *env)
{
	t_var	var;
	t_milieu	p;

	var = init_var(a, b);
	while (1)
	{
		p.x = var.x;
		p.y = var.y;
		if (outside_window(env, p.x, p.y))
			draw_pixel(env, p.x, p.y, (t_color) {0, 255, 0});
	if (var.x >= b.x && var.y >= b.y)
		break ;
		var.e2 = var.error;
		if (var.e2 > -var.dx)
		{
			var.error -= var.dy;
			var.x += var.sx;
		}
		if (var.e2 < var.dy)
		{
			var.error += var.dx;
			var.y += var.sy;
		}
	}
}

void	flocon(t_env *e)
{
	t_milieu	mil;
	t_milieu	a;
	t_milieu	b;
	t_milieu	c;

	mil.x = e->longer / 2;
	mil.y = e->large / 2;
	a.x = mil.x;
	a.y = mil.y - (1 * (e->longer / 4));
	b.x = mil.x + ((sqrt(3) / 2) * (e->large / 4));
	b.y = mil.y + ((1 / 2) * (e->longer / 4));
	c.x = mil.x - ((sqrt(3) / 2) * (e->large / 4));
	c.y = mil.y - ((1 / 2) * (e->longer / 4));
	draw_pixel(e, a.x, a.y, (t_color) {0, 255, 0});
	draw_pixel(e, b.x, b.y, (t_color) {0, 255, 0});
	draw_pixel(e, c.x, c.y, (t_color) {0, 255, 0});

	print_line(a, b, e);
	print_line(c, a, e);
	print_line(c, b, e);



}
