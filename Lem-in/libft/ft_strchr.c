/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strchr.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 13:01:35 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/03 18:50:47 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

char	*ft_strchr(const char *s, int c)
{
	char	test;
	int		i;

	i = 0;
	test = (char)c;
	while (s[i] != '\0')
	{
		if (s[i] == test)
			return ((char *)&s[i]);
		i++;
	}
	if (s[i] == test)
		return ((char *)&s[i]);
	return (NULL);
}
