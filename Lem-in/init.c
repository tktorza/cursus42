/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   init.c                                             :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:55:30 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:55:31 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

void			init_iter(t_iter *iter)
{
	iter->i = 0;
	iter->j = 0;
	iter->k = 0;
	iter->str = NULL;
	iter->tmp = NULL;
}

int				ft_check1(char *str, char *tmp)
{
	int			i;
	int			j;
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	i = go_link(tmp, ft_strlen(tmp));
	j = go_link(str, ft_strlen(str));
	iter->tmp = half_link(tmp, i);
	iter->str = half_link(str, j);
	if (ft_strcmp(iter->str, iter->tmp) == 0)
	{
		free(iter);
		iter = NULL;
		return (0);
	}
	free(iter);
	iter = NULL;
	return (-1);
}

int				ft_check2(char *str, char *tmp)
{
	int			i;
	int			j;
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	j = 0;
	i = go_link(tmp, ft_strlen(tmp));
	while (str[j] != '-')
		j++;
	iter->tmp = half_link(tmp, i);
	iter->str = (char *)malloc(sizeof(char) * j);
	while (str[iter->k] != '-')
		iter->str[iter->j++] = str[iter->k++];
	iter->str[iter->j] = '\0';
	if (ft_strcmp(iter->str, iter->tmp) == 0)
	{
		free(iter);
		iter = NULL;
		return (0);
	}
	free(iter);
	iter = NULL;
	return (-1);
}

int				ft_check_order(char *str, char *tmp)
{
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	while (tmp[iter->i] != '-')
		iter->i++;
	iter->str = (char *)malloc(sizeof(char) *
		((int)ft_strlen(str) - iter->i + 1));
	iter->tmp = (char *)malloc(sizeof(char) *
		((int)ft_strlen(str) - iter->i + 1));
	while (tmp[iter->i++] != '\0')
		iter->str[iter->j++] = tmp[iter->i];
	iter->str[iter->j] = '\0';
	iter->i = (int)ft_strlen(str) - 1;
	while (str[iter->i] != '-')
		iter->i--;
	while (str[iter->i++] != '\0')
		iter->tmp[iter->k++] = str[iter->i];
	iter->tmp[iter->k] = '\0';
	if (ft_strcmp(iter->tmp, iter->str) == 0)
	{
		iter = free_iter(iter);
		return (1);
	}
	iter = free_iter(iter);
	return (0);
}

char			*ft_copy(char *str, char *tmp)
{
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	while (tmp[iter->i] != '-')
		iter->i++;
	iter->str = (char *)malloc(sizeof(char) *
		((int)ft_strlen(str) - iter->i + 1));
	if (ft_check_order(str, tmp) == 1)
	{
		while (tmp[iter->j] != '-')
		{
			iter->str[0] = '-';
			iter->str[iter->k + 1] = tmp[iter->j];
			iter->k++;
			iter->j++;
		}
		iter->str[iter->k + 1] = '\0';
		return (ft_strjoin(str, iter->str));
	}
	while (tmp[iter->i] != '\0')
		iter->str[iter->k++] = tmp[iter->i++];
	iter->str[iter->k] = '\0';
	return (ft_strjoin(str, iter->str));
}
