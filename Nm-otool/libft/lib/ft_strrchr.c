/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strrchr.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/24 10:40:18 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:21 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

char	*ft_strrchr(const char *s, int c)
{
	char	*sav;

	sav = NULL;
	while (*s != '\0')
	{
		if (*s == (char)c)
			sav = (char *)s;
		s++;
	}
	if (*s == (char)c)
		sav = (char *)s;
	return (sav);
}
