/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_display.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:54:35 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:54:36 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

void		ft_do(t_aff *new, t_bloc *bloc)
{
	while (new->next)
	{
		bloc = g_bloc;
		if (ft_strcmp(new->room, g_end) != 0)
		{
			new->room = room_next(new->room, new->bloc);
		}
		if (ft_strcmp(new->room, g_end) == 0)
		{
			while (ft_strcmp(new->bloc, bloc->str) != 0)
				bloc = bloc->next;
			g_ant -= 1;
			new->final = 10;
		}
		new = new->next;
	}
}

t_aff		*ft_control(int *nb, t_bloc *bloc, t_aff *new)
{
	t_aff	*origin;
	t_aff	*nok;
	t_aff	*tmp;

	tmp = (t_aff *)malloc(sizeof(t_aff));
	new = delete_end(new);
	origin = new;
	g_bloc = bloc;
	ft_do(new, bloc);
	tmp = new_aff(tmp, g_bloc, nb);
	if (origin)
	{
		nok = origin;
		while ((origin->next)->next)
			origin = origin->next;
		origin->next = tmp;
		return (nok);
	}
	else
		origin = tmp;
	return (origin);
}

void		ft_disp(t_bloc *bloc, t_aff *new, t_aff *aff, int nb)
{
	while (bloc->next)
	{
		bloc->ant_nb -= 1;
		aff->bloc = bloc->str;
		aff->index = nb;
		aff->room = room_next(g_start, bloc->str);
		aff = aff_next(aff);
		nb++;
		bloc = bloc->next;
	}
	aff->next = NULL;
	bloc = g_bloc;
	nb--;
	display_on(new);
	while (g_ant > 0)
	{
		new = ft_control(&nb, bloc, new);
		display_on(new);
	}
}

void		ft_display(t_bloc *bloc)
{
	t_aff	*new;
	t_aff	*aff;
	int		nb;

	ft_printf("\nFor {yellow}%d{eoc} number of stroke\n\n",
		bloc->ant_nb + bloc->nb);
	g_bloc = (t_bloc *)malloc(sizeof(t_bloc));
	new = (t_aff *)malloc(sizeof(t_aff));
	aff = (t_aff *)malloc(sizeof(t_aff));
	aff->next = (t_aff *)malloc(sizeof(t_aff));
	g_bloc = bloc;
	new = aff;
	nb = 1;
	ft_disp(bloc, new, aff, nb);
}
