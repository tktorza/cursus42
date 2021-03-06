/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strlwr.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/12/17 10:30:04 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:45 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <string.h>

static int		pf_ft_isupper(int c)
{
	return ((unsigned char)c >= 'A' && (unsigned char)c <= 'Z');
}

static int		pf_ft_tolower(int c)
{
	if (c >= 65 && c <= 90)
		return (c + 32);
	return (c);
}

char			*pf_ft_strlwr(char *s1)
{
	int		i;

	i = 0;
	if (!s1)
		return (NULL);
	while (s1[i] != '\0')
	{
		if (pf_ft_isupper(s1[i]))
			s1[i] = pf_ft_tolower(s1[i]);
		i++;
	}
	return (s1);
}
