/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_printf_anexes_1.c                               :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 15:25:03 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 14:33:28 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

void			ft_initialize(const char *s)
{
	g_s = s;
	g_moins = 0;
	g_total = 0;
}

t_buff			*cas_1(int *i, t_buff *arg, t_buff *new)
{
	arg = ft_pursuit(g_s, i, arg);
	arg->next = new;
	return (new);
}

void			ft_etoile(const char *s, int *i)
{
	if (s[*i] == '*')
		*i += 1;
}

static int		ft_presence(int i)
{
	while (g_s[i] && g_s[i] != '*')
		i--;
	if (g_s[i + 1] && g_s[i + 1] >= 48 && g_s[i + 1] <= 57)
		return (1);
	return (0);
}

void			ft_stars(int *i, t_char *all, t_chain *ap, int tab[8])
{
	all->star = ft_atoi(ap->arg);
	if (all->star < 0)
	{
		tab[2] = 1;
		g_moins = 1;
		all->star *= -1;
	}
	if (ft_presence(*i) == 1)
		all->large = ft_largeur(tab, all, g_s, *i);
	else
		all->large = ft_spec_large(tab, all, g_s, *i);
	if (ft_presence_star(g_s, *i) == 2)
		*ap = *ap->next;
	else if (ft_presence_star(g_s, *i) == 3)
		*ap = *(ap->next)->next;
}
