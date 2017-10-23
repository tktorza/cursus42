/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   sort.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/18 13:15:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 13:37:35 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/nm_tool.h"

static struct nlist_64     *fill_array_64(struct nlist_64 *tab, int taille)
{
    struct nlist_64 *tab2;
    int             i;

    tab2 = (struct nlist_64*)malloc(sizeof(struct nlist_64) * taille);
    i = 0;
    while (i < taille)
    {
        tab2[i] = tab[i];
        i++;
    }
    return (tab2);
}

static struct nlist     *fill_array(struct nlist *tab, int taille)
{
    struct nlist *tab2;
    int             i;

    tab2 = (struct nlist*)malloc(sizeof(struct nlist) * taille);
    i = 0;
    while (i < taille)
    {
        tab2[i] = tab[i];
        i++;
    }
    return (tab2);
}

struct nlist_64     *tri_bulle_64(char *stringtable, struct nlist_64 *tab, int taille)
{
    struct nlist_64 *tab2;
    struct nlist_64 temp;
    int i;
    int j;

    i = 0;
    tab2 = fill_array_64(tab, taille);
    while (i < taille)
    {
        j = 0;
        while(j < taille)
        {
            if (ft_strcmp(stringtable + tab2[i].n_un.n_strx,
                stringtable + tab2[j].n_un.n_strx) < 0)
            {
                temp = tab2[i];
                tab2[i] = tab2[j];
                tab2[j] = temp;
            }
            j++;
        }
        i++;
    }
    return (tab2);
}

struct nlist     *tri_bulle(char *stringtable, struct nlist *tab, int taille)
{
    struct nlist *tab2;
    struct nlist temp;
    int i;
    int j;

    i = 0;
    tab2 = fill_array(tab, taille);
    while (i < taille)
    {
        j = 0;
        while(j < taille)
        {
            if (ft_strcmp(stringtable + tab2[i].n_un.n_strx,
                stringtable + tab2[j].n_un.n_strx) < 0)
            {
                temp = tab2[i];
                tab2[i] = tab2[j];
                tab2[j] = temp;
            }
            j++;
        }
        i++;
    }
    return (tab2);
}
