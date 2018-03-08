/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_split.c                                         :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/03/14 12:38:25 by tktorza           #+#    #+#             */
/*   Updated: 2016/04/19 15:05:56 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "includes/libft.h"

static int	ft_numberisation(char *str, int x)
{
	int		nb;

	nb = 0;
	while (str[x] && str[x] != ' ' && str[x] != '	' && str[x] != '\n')
	{
		nb++;
		x++;
	}
	return (nb);
}

static int	ft_words(char *str)
{
	int		i;
	int		nb;

	i = 0;
	nb = 0;
	while (str[i])
	{
		if (str[i] != ' ' && str[i] != '	' && str[i] != '\n')
			if (str[i + 1] == ' ' || str[i + 1] == '	' \
					|| str[i + 1] == '\n' || str[i + 1] == '\0')
				nb++;
		i++;
	}
	return (nb);
}

static char	*ft_algo(char *str, int *x)
{
	int		i;
	char	*tab;

	i = 0;
	tab = (char *)malloc(sizeof(char) * ft_numberisation(str, *x));
	while (str[*x] && str[*x] != ' ' && str[*x] != '	' && str[*x] != '\n')
	{
		tab[i] = str[*x];
		i++;
		*x += 1;
	}
	tab[i] = '\0';
	return (tab);
}

char		**ft_split(char *str)
{
	char	**tab;
	int		i;
	int		x;

	x = 0;
	i = 0;
	if (str != NULL)
	{
		tab = (char **)malloc(sizeof(char *) * (ft_words(str) + 1));
		while (str[x])
		{
			if (str[x] != ' ' && str[x] != '	' && str[x] != '\n')
				tab[i++] = ft_algo(str, &x);
			if (str[x])
				x++;
		}
		tab[i] = NULL;
		return (tab);
	}
	return (NULL);
}
