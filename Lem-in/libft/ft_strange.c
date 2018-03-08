/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strange.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 15:08:27 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 14:39:12 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*ft_spec_large(int tab[8], t_char *all, const char *s, int i)
{
	if (all->preci[0] == '\0' && (s[i] == 'c' || s[i] == 'C'))
	{
		all->large = (char *)malloc(sizeof(char) * (all->star - 1));
		if (tab[1] == 1 && tab[2] != 1 && tab[6] == -10)
			all->large = ft_remplissage('0', ((all->star) - 1\
						- ft_strlen(all->hexa)));
		else
			all->large = ft_remplissage(' ', ((all->star) - 1\
						- ft_strlen(all->hexa)));
	}
	else
	{
		all->large = (char *)malloc(sizeof(char) * \
				((all->star) - ft_strlen(all->preci)));
		if (tab[1] == 1 && tab[2] != 1 && tab[6] == -10)
			all->large = ft_remplissage('0', ((all->star) - \
						ft_strlen(all->preci) - ft_strlen(all->hexa)));
		else
			all->large = ft_remplissage(' ', ((all->star) - \
						ft_strlen(all->preci)\
						- ft_strlen(all->hexa)));
	}
	return (all->large);
}

char	*ft_c_large(int tab[8], t_char *all)
{
	all->large = (char *)malloc(sizeof(char) * \
			(tab[5] - ft_strlen(all->preci)));
	if (tab[1] == 1 && tab[2] != 1)
		all->large = ft_remplissage('0', (tab[5] - ft_strlen(all->preci)\
					- ft_strlen(all->hexa)));
	else
		all->large = ft_remplissage(' ', (tab[5] - ft_strlen(all->preci)\
					- ft_strlen(all->hexa)));
	return (all->large);
}

char	*ft_largeur(int tab[8], t_char *all, const char *s, int i)
{
	if (all->preci[0] == '\0' && (s[i] == 'c' || s[i] == 'C'))
	{
		all->large = (char *)malloc(sizeof(char) * (tab[5] - 1));
		if (tab[1] == 1 && tab[2] != 1)
			all->large = ft_remplissage('0', (tab[5] - 1\
						- ft_strlen(all->hexa)));
		else
			all->large = ft_remplissage(' ', (tab[5] - 1\
						- ft_strlen(all->hexa)));
	}
	else if ((s[i] == 'c' || s[i] == 'C') && all->preci[0] != '\0')
		all->large = ft_c_large(tab, all);
	else
	{
		all->large = (char *)malloc(sizeof(char) * \
				(tab[5] - ft_strlen(all->preci)));
		if (tab[1] == 1 && tab[2] != 1 && (tab[6] == -10 || (s[i] == 's' \
				|| s[i] == 'S' || ft_presence_star(s, i) > 0)))
			all->large = ft_remplissage('0', (tab[5] - ft_strlen(all->preci)\
						- ft_strlen(all->hexa)));
		else
			all->large = ft_remplissage(' ', (tab[5] - ft_strlen(all->preci)\
						- ft_strlen(all->hexa)));
	}
	return (all->large);
}

int		ft_presence_star(const char *s, int i)
{
	i--;
	while (s[i] && ft_strchr(ft_strdup("*hljz0123456789."), s[i]) != 0 \
			&& s[i] != '%')
	{
		if (s[i] == '*')
		{
			if (s[i - 1] == '.')
			{
				if (s[i - 2] == '*')
					return (3);
				return (1);
			}
			return (2);
		}
		i--;
	}
	return (0);
}

char	*final_arg(const char *s, int i, t_chain ap, t_char *all)
{
	if (ft_presence_star(s, i) == 2 || ft_presence_star(s, i) == 1)
		all->arg = (ap.next)->arg;
	else if (ft_presence_star(s, i) == 3)
		all->arg = ((ap.next)->next)->arg;
	else if (ft_presence_star(s, i) == 0)
		all->arg = ap.arg;
	return (all->arg);
}
