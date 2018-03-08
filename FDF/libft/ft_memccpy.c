/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_memccpy.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/27 14:02:14 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/09 18:16:52 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

void	*ft_memccpy(void *dest, const void *src, int c, size_t n)
{
	size_t				i;
	unsigned char		*str;
	unsigned char		*src2;
	unsigned char		c2;

	i = 0;
	c2 = (unsigned char)c;
	str = (unsigned char *)dest;
	src2 = (unsigned char *)src;
	while (i < n)
	{
		if (src2[i] == c2)
		{
			str[i] = src2[i];
			return (dest + i + 1);
		}
		str[i] = src2[i];
		i++;
	}
	return (NULL);
}
