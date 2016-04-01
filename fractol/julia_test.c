#include "Includes/fractol.h"

static int	ft_ok(t_tools t)
{
	t.ux = ((t.x / 400) * 2.5) - 1.25;
	t.uy = ((t.y / 400) * 2.5) - 1.25;
t.i = 0;
		t.new_re = t.prev_re * t.prev_re + t.c_re;
		t.new_im = t.prev_im * t.prev_im + t.c_im;
	while (t.i < 200)
	{
		t.new_re = t.prev_re * t.prev_re + t.c_re;
		t.new_im = t.prev_im * t.prev_im + t.c_im;
		if (t.ux == t.prev_re && t.uy == t.prev_im)
			return (1);
	//printf("t.ux = {%f} prev_re {%f} prev_im = {%f} new_re = {%f} new_im = {%f}\n", t.ux, t.prev_re, t.prev_im, t.new_re, t.new_im);
		t.prev_re = t.new_re;
		t.prev_im = t.new_im;
		t.i += 1;
	}
	return (0);
}

void	julia(t_env *e)
{
	t_tools		t;

	t.y = 0;
	t.c_re = 0.3;
	t.c_im = 0.5;

	while (t.y < e->longer)
	{
		t.x = 0;
		while (t.x < e->large)
		{
//printf("ici\n");
			t.prev_re = 0;
			t.prev_im = 0;
			if (ft_ok(t) == 1)
				{
//printf("ici\n");
draw_pixel(e, t.x, t.y, (t_color) {255, 255, 255});
			}
			else
draw_pixel(e, t.x, t.y, (t_color) {0, 255, 0});
			t.x += 1;
		}
		t.y += 1;
	}

}
