/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_color.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/09 16:37:33 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/12 15:37:44 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

static char		*ft_coloriage(int i)
{
	char **tab;

	tab = (char **)malloc(sizeof(char *) * 10);
	tab[0] = ft_strdup("\x1B[34m");
	tab[1] = ft_strdup("\x1B[31m");
	tab[2] = ft_strdup("\x1B[32m");
	tab[3] = ft_strdup("\x1B[33m");
	tab[4] = ft_strdup("\x1B[30m");
	tab[5] = ft_strdup("\x1B[37m");
	tab[6] = ft_strdup("\x1B[36m");
	tab[7] = ft_strdup("\x1B[35m");
	tab[8] = ft_strdup("\x1B[0m");
	tab[9] = NULL;
	return (tab[i]);
}

static char		**tab_color(void)
{
	char	**tab;

	tab = (char **)malloc(sizeof(char *) * 10);
	tab[0] = ft_strdup("{blue}");
	tab[1] = ft_strdup("{red}");
	tab[2] = ft_strdup("{green}");
	tab[3] = ft_strdup("{yellow}");
	tab[4] = ft_strdup("{black}");
	tab[5] = ft_strdup("{white}");
	tab[6] = ft_strdup("{cyan}");
	tab[7] = ft_strdup("{magenta}");
	tab[8] = ft_strdup("{eoc}");
	tab[9] = NULL;
	return (tab);
}

char			*ft_coloriste(const char *s, int i)
{
	int		save;
	char	*str;
	char	**tab;

	save = i;
	tab = tab_color();
	if (s[i] == '{')
	{
		while (s[i] && s[i] != '%' && s[i] != '}')
			i++;
		str = ft_strnew(i - save + 2);
		i = save;
		save = 0;
		while (s[i] && s[i] != '%' && s[i] != '}')
			str[save++] = s[i++];
		if (s[i] == '}')
			str[save++] = s[i];
		str[save] = '\0';
		i = -1;
		while (tab[++i])
			if (ft_strcmp(str, tab[i]) == 0)
				return (str);
	}
	return (NULL);
}

t_buff			*ft_color(const char *s, int *i, t_buff *arg)
{
	char	**tab;
	char	*str;
	int		x;
	t_buff	*new;

	x = 0;
	new = (t_buff *)malloc(sizeof(t_buff));
	arg->next = (t_buff *)malloc(sizeof(t_buff));
	arg->next = new;
	tab = tab_color();
	str = ft_coloriste(s, *i);
	while (tab[x])
	{
		if (ft_strcmp(tab[x], str) == 0)
		{
			arg->buf = ft_coloriage(x);
			arg->c = 'y';
			while (s[*i] != '}')
				*i += 1;
			return (new);
		}
		x++;
	}
	return (0);
}
