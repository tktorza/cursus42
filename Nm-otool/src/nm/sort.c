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

static struct nlist_64     *fill_array_64(struct nlist_64 *tab, uint32_t taille)
{
    struct nlist_64 *tab2;
    uint32_t             i;

    tab2 = (struct nlist_64*)malloc(sizeof(struct nlist_64) * taille);
    i = 0;
    while (i < taille)
    {
        tab2[i] = tab[i];
        i++;
    }
    return (tab2);
}

static struct nlist     *fill_array(struct nlist *tab, uint32_t taille)
{
    struct nlist *tab2;
    uint32_t             i;

    tab2 = (struct nlist*)malloc(sizeof(struct nlist) * taille);
    i = 0;
    while (i < taille)
    {
        tab2[i] = tab[i];
        i++;
    }
    return (tab2);
}

struct nlist_64     *tri_by_value_64(char *stringtable, struct nlist_64 *tab, uint32_t taille)
{
    uint32_t i;
    uint32_t j;
    struct nlist_64 tmp;

    i = 0;
    while (i < taille - 1)
    {
        j = i + 1;
        while(j < taille)
        {
            if (ft_strcmp(stringtable + tab[i].n_un.n_strx,
                stringtable + tab[j].n_un.n_strx) == 0 &&
                tab[j].n_value < tab[i].n_value)
            {
                tmp = tab[j];
                tab[j] = tab[i];
                tab[i] = tmp;
                return (tri_by_value_64(stringtable, tab, taille));
            }
            j++;
        }
        i++;
    }
    return (tab);
}

struct nlist_64     *tri_bulle_64(char *stringtable, struct nlist_64 *tab, uint32_t taille)
{
    struct nlist_64 *tab2;
    struct nlist_64 temp;
    uint32_t i;
    uint32_t j;

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
    return (tri_by_value_64(stringtable, tab2, taille));
}

struct nlist     *tri_by_value(char *stringtable, struct nlist *tab, uint32_t taille)
{
    uint32_t i;
    uint32_t j;
    struct nlist tmp;

    i = 0;
    while (i < taille - 1)
    {
        j = i + 1;
        while(j < taille)
        {
            if (ft_strcmp(stringtable + tab[i].n_un.n_strx,
                stringtable + tab[j].n_un.n_strx) == 0 &&
                tab[j].n_value < tab[i].n_value)
            {
                tmp = tab[j];
                tab[j] = tab[i];
                tab[i] = tmp;
                return (tri_by_value(stringtable, tab, taille));
            }
            j++;
        }
        i++;
    }
    return (tab);
}

struct nlist     *tri_bulle(char *stringtable, struct nlist *tab, uint32_t taille)
{
    struct nlist *tab2;
    struct nlist temp;
    uint32_t i;
    uint32_t j;

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
    return (tri_by_value(stringtable, tab2, taille));
}
