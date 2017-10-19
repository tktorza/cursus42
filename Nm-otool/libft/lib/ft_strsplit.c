/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strsplit.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 16:13:53 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:23 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

static int	ft_cntwords(char const *s, char c)
{
	int		words;

	words = 0;
	while (*s != '\0')
	{
		if (*s != c)
			words++;
		while (*s != c && *s)
			s++;
		while (*s == c && *s)
			s++;
	}
	return (words);
}

static int	ft_lenwords(char *s, char c)
{
	int		len;

	len = 0;
	while (*s != c && *s != '\0')
	{
		s++;
		len++;
	}
	return (len);
}

char		**ft_strsplit(char const *s, char c)
{
	int		words;
	char	**tab;
	int		i;

	i = 0;
	words = ft_cntwords((char *)s, c);
	tab = (char **)malloc(sizeof(char *) * (words + 1));
	if (!tab)
		return (NULL);
	tab[words] = NULL;
	while (words--)
	{
		while (*s == c && *s != '\0')
			s++;
		tab[i] = ft_strsub((char *)s, 0, ft_lenwords((char *)s, c));
		if (!tab[i])
			return (NULL);
		s = s + ft_lenwords((char *)s, c);
		i++;
	}
	return (tab);
}
