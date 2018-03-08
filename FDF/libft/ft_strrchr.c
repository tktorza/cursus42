/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strrchr.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 13:37:55 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/03 18:48:04 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

char	*ft_strrchr(const char *s, int c)
{
	size_t	tall;
	char	test;

	test = (char)c;
	tall = ft_strlen(s);
	while (tall > 0)
	{
		if (s[tall] == test)
			return ((char *)&s[tall]);
		tall--;
	}
	if (s[tall] == test)
		return ((char *)&s[tall]);
	return (NULL);
}
