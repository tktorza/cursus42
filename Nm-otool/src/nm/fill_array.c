/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   fill_array.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/27 13:39:05 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/27 13:39:06 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../includes/nm_tool.h"

int			search_lst(t_offlist *lst, uint32_t off)
{
	t_offlist	*cur;

	cur = lst;
	while (cur)
	{
		if (cur->off == off)
			return (1);
		cur = cur->next;
	}
	return (0);
}

struct nlist_64	*fill_array_64(struct nlist_64 *tab, uint32_t taille)
{
	struct nlist_64	*tab2;
	uint32_t		i;

	tab2 = (struct nlist_64 *)malloc(sizeof(struct nlist_64) * taille);
	i = 0;
	while (i < taille)
	{
		tab2[i] = tab[i];
		i++;
	}
	return (tab2);
}

struct nlist	*fill_array(struct nlist *tab, uint32_t taille)
{
	struct nlist	*tab2;
	uint32_t		i;

	tab2 = (struct nlist *)malloc(sizeof(struct nlist) * taille);
	i = 0;
	while (i < taille)
	{
		tab2[i] = tab[i];
		i++;
	}
	return (tab2);
}
