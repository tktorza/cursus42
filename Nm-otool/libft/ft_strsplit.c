/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strsplit.c                                      :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/27 16:51:12 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/03 18:51:55 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

static char	**ft_finalize(char **final, int size, char const *s, char c)
{
	int		i;
	int		y;

	i = 0;
	while (i != size)
	{
		y = 0;
		if (*s == c)
			s++;
		else
		{
			y = 0;
			while (s[y] && s[y] != c)
				y++;
			final[i] = ft_strsub(s, 0, y);
			i++;
			s += y;
		}
	}
	final[i] = NULL;
	return (final);
}

char		**ft_strsplit(char const *s, char c)
{
	int		i;
	char	**final;

	i = ft_word_number(s, c);
	final = (char **)malloc(sizeof(char *) * (i + 1));
	if (final == NULL)
		return (NULL);
	final = ft_finalize(final, i, s, c);
	return (final);
}
