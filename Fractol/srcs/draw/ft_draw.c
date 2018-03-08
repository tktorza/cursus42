/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_draw.c                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:58:40 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:39:08 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

void		draw_pixel(t_env *e, int x, int y, t_color color)
{
	e->pixel_image =
		mlx_get_data_addr(e->img, &(e->bpp), &(e->s_line), &(e->ed));
	e->pixel_image[x * e->bpp / 8 + y * e->s_line] = (unsigned char)color.b;
	e->pixel_image[x * e->bpp / 8 + 1 + y * e->s_line] = (unsigned char)color.g;
	e->pixel_image[x * e->bpp / 8 + 2 + y * e->s_line] = (unsigned char)color.r;
}

void		draw_julia(t_env *e)
{
	pthread_t	th1;
	pthread_t	th2;
	pthread_t	th3;
	pthread_t	th4;

	if (pthread_create(&th1, NULL, draw_2, e))
		exit(0);
	if (pthread_create(&th2, NULL, draw_1, e))
		exit(0);
	if (pthread_create(&th3, NULL, draw_3, e))
		exit(0);
	if (pthread_create(&th4, NULL, draw_4, e))
		exit(0);
	if (pthread_join(th1, NULL))
		exit(0);
	if (pthread_join(th2, NULL))
		exit(0);
	if (pthread_join(th3, NULL))
		exit(0);
	if (pthread_join(th4, NULL))
		exit(0);
}

void		draw_mandel(t_env *e)
{
	pthread_t	th1;
	pthread_t	th2;
	pthread_t	th3;

	if (pthread_create(&th1, NULL, mandel_draw_1, e))
		exit(0);
	if (pthread_create(&th2, NULL, mandel_draw_2, e))
		exit(0);
	if (pthread_create(&th3, NULL, mandel_draw_3, e))
		exit(0);
	if (pthread_join(th1, NULL))
		exit(0);
	if (pthread_join(th2, NULL))
		exit(0);
	if (pthread_join(th3, NULL))
		exit(0);
}
