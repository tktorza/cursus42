/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   utils3.c                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:56:41 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:56:42 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

void		color_ant(int nb)
{
	nb % 7 == 0 ? ft_printf("{blue}%d{eoc}", nb) : nb;
	nb % 7 == 1 ? ft_printf("{red}%d{eoc}", nb) : nb;
	nb % 7 == 2 ? ft_printf("{green}%d{eoc}", nb) : nb;
	nb % 7 == 3 ? ft_printf("{yellow}%d{eoc}", nb) : nb;
	nb % 7 == 4 ? ft_printf("{white}%d{eoc}", nb) : nb;
	nb % 7 == 5 ? ft_printf("{cyan}%d{eoc}", nb) : nb;
	nb % 7 == 6 ? ft_printf("{magenta}%d{eoc}", nb) : nb;
}

void		display_on(t_aff *new)
{
	while (new && new->next)
	{
		ft_putchar('L');
		color_ant(new->index);
		ft_printf("-%s ", new->room);
		new = new->next;
	}
	ft_printf("\n");
}

char		*room_next(char *s1, char *s2)
{
	int		i;

	i = 0;
	while (s2[i])
	{
		if (ft_strcmp(ft_cuter(s2, i), s1) == 0)
			return (ft_cuter(s2, i + 1 + ft_strlen(ft_cuter(s2, i))));
		i++;
	}
	return (NULL);
}

int			indexation(t_aff *new)
{
	while (new->next)
		new = new->next;
	return (new->index + 1);
}

int			ft_origin_check(t_file *origin)
{
	t_file	*new;
	t_file	*o;
	int		i;

	o = origin;
	while (origin->next)
	{
		i = 2;
		new = o;
		while (new->next && new->next->name)
		{
			if (ft_strcmp(new->name, origin->name) == 0)
			{
				new = new->next;
				i--;
			}
			new = new->next;
		}
		if (i <= 0)
			return (1);
		origin = origin->next;
	}
	return (0);
}
