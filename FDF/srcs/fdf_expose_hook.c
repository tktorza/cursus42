/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fdf_expose_hook.c                                  :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/18 15:34:00 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/22 16:21:44 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/fdf.h"

void	colorr(t_env *e, int x, int y, int z)
{
	e->c1 = x;
	e->c2 = y;
	e->c3 = z;
}

void	colorate(t_env *e)
{
	if (e->height == 1)
		colorr(e, 121, 121, 121);
	if (e->height == 0)
		colorr(e, 100, 100, 100);
	if (e->height == 2)
		colorr(e, 144, 222, 111);
	if (e->height > 2 && e->height < 4)
		colorr(e, 222, 222, 211);
	if (e->height == -2)
		colorr(e, 255, 0, 10);
	if (e->height < -2)
		colorr(e, 25, 250, 21);
	if (e->height > 4)
		colorr(e, 223, 250, 211);
}

int		ft_zoom(t_env *e)
{
	e->zoom = 30;
	while (e->zoom > 1 && (e->zoom * (e->xmax * e->zoom * e->ymax * e->zoom)) \
			> e->large * e->longer)
		e->zoom -= 1;
	return (e->zoom);
}

void	e_initialize(t_env *e)
{
	e->onetwo = 0;
	e->pts = 0;
	e->large = 2400;
	e->longer = 2000;
	e->height = 1;
	e->esc_up = 400;
	e->esc_right = 1200;
	e->zoom = ft_zoom(e);
	colorate(e);
}

int		expose_hook(t_env *e)
{
	t_line *line;

	line = (t_line *)malloc(sizeof(t_line));
	line->next = (t_line *)malloc(sizeof(t_line));
	line = e->line;
	colorate(e);
	e->image = mlx_new_image(e->mlx, e->large, e->longer);
	ft_anim(e);
	fdf_draw(line, e);
	mlx_put_image_to_window(e->mlx, e->win, e->image, 0, 0);
	mlx_destroy_image(e->mlx, e->image);
	return (0);
}
