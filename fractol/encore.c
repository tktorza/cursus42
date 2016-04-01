#include "Includes/fractol.h"



void	mandelbrot(t_env *e)
{
	double	x1 = -2.1;
	double	x2 = 0.6;
	double	y1 = -1.2;
	double	y2 = 1.2;
	double	it_max = 50;

	double	image_x = (x2 - x1) * e->zoom;
	double	image_y = (y2 - y1) * e->zoom;
	double x = 0;
	double y;

	while (x < image_x)
	{
		y = 0;
		while (y < image_y)
		{
			double c_r = x / e->zoom + x1;
			double  c_i = y / e->zoom + y1;
			double z_r = 0;
			double z_i = 0;
			double i = 0;
			while (z_r*z_r + z_i*z_i < 4 && i < it_max)
			{
			double tmp = z_r;
			z_r = z_r*z_r - z_i*z_i + c_r;
			z_i = 2*z_i*tmp + c_i;
				i++;
	}	
			if (i == it_max)
					draw_pixel(e, x + e->esc_right, y + e->esc_up, (t_color) {0, 100 * y, 200 / x});
			else
					draw_pixel(e, x + e->esc_right, y + e->esc_up, (t_color) {255*i/it_max, 0, 0});
				
			y++;
		}
		x++;
	}
}
