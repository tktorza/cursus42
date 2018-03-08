/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_hook_frtl_2.c                                   :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 16:00:09 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:00:15 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

int				mouse_motion(int x, int y, t_env *e)
{
	if (x >= 0 && x <= WIDTH && y >= 0 && y <= HEIGHT && e->mouse_event)
	{
		e->real = (double)x / (double)WIDTH * 4 - 2;
		e->imnbr = (double)y / (double)HEIGHT * 4 - 2;
		expose_hook(e);
	}
	return (0);
}

int				mouse_hook(int button, int x, int y, t_env *e)
{
	x = 0;
	y = 0;
	if (button == 4)
		e->zoom = e->zoom * 1.2;
	if (button == 5)
		e->zoom = e->zoom / 1.2;
	expose_hook(e);
	return (0);
}
