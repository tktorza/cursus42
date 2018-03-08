/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   get_color.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/04/26 15:59:45 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/26 16:22:34 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/fractol.h"

t_color		get_color(t_color color, t_env *e, int i, float c)
{
	c = 0;
	color = (t_color) {((30 + e->ajust) % 255) * i, \
		((25 + e->ajust) % 255) * i, (255) * i * e->ajust};
	return (color);
}
