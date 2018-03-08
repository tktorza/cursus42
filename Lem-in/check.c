/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   check.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/24 15:53:37 by tktorza           #+#    #+#             */
/*   Updated: 2016/05/24 15:53:39 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "lemin.h"

int				check_start(char *str)
{
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	iter->str = (char *)malloc(sizeof(char) * (ft_strlen(str) - 1));
	iter->tmp = (char *)malloc(sizeof(char) * (ft_strlen(str) - 1));
	while (str[iter->i] != '-')
		iter->str[iter->j++] = str[iter->i++];
	iter->i++;
	iter->str[iter->j] = '\0';
	while (str[iter->i] != '\0')
	{
		iter->tmp[iter->k++] = str[iter->i];
		iter->i++;
	}
	iter->tmp[iter->k] = '\0';
	if (ft_strcmp(iter->str, g_start) == 0
		|| ft_strcmp(iter->tmp, g_start) == 0)
	{
		free(iter);
		iter = NULL;
		return (1);
	}
	free(iter);
	iter = NULL;
	return (0);
}

int				check_start2(char *str)
{
	int			i;
	int			j;
	char		*tmp1;

	i = 0;
	j = 0;
	tmp1 = (char *)malloc(sizeof(char) * (ft_strlen(str) - 1));
	while (str[i] != '-')
		i++;
	i++;
	while (str[i] != '\0')
	{
		tmp1[j++] = str[i];
		i++;
	}
	tmp1[j] = '\0';
	if (ft_strcmp(tmp1, g_start) == 0)
	{
		free(tmp1);
		tmp1 = NULL;
		return (1);
	}
	free(tmp1);
	tmp1 = NULL;
	return (0);
}

int				check_end(char *str)
{
	t_iter		*iter;

	init_iter(iter = (t_iter *)malloc(sizeof(t_iter)));
	iter->str = (char *)malloc(sizeof(char) * (ft_strlen(str) - 1));
	iter->tmp = (char *)malloc(sizeof(char) * (ft_strlen(str) - 1));
	while (str[iter->i] != '-')
		iter->str[iter->j++] = str[iter->i++];
	iter->str[iter->j] = '\0';
	while (str[iter->i + 1] != '\0')
	{
		iter->tmp[iter->k++] = str[iter->i + 1];
		iter->i++;
	}
	iter->tmp[iter->k] = '\0';
	if (ft_strcmp(iter->str, g_end) == 0 || ft_strcmp(iter->tmp, g_end) == 0)
	{
		free(iter);
		iter = NULL;
		return (1);
	}
	free(iter);
	iter = NULL;
	return (0);
}

int				check_end2(char *str)
{
	int			i;
	char		*tmp;
	int			j;

	j = 0;
	i = (int)ft_strlen(str);
	while (str[i] != '-')
		i--;
	i++;
	tmp = (char *)malloc(sizeof(char) * (ft_strlen(str) - i));
	while (str[i] != '\0')
	{
		tmp[j] = str[i];
		j++;
		i++;
	}
	if (ft_strcmp(tmp, g_end) == 0)
	{
		free(tmp);
		tmp = NULL;
		return (1);
	}
	free(tmp);
	tmp = NULL;
	return (0);
}
