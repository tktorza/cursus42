/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_memccpy.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/24 18:17:49 by tktorza           #+#    #+#             */
/*   Updated: 2017/11/23 16:24:03 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

void			*ft_memccpy(void *dst, const void *src, int c, size_t n)
{
	unsigned char		*pdst;
	unsigned char		*psrc;

	pdst = (unsigned char *)dst;
	psrc = (unsigned char *)src;
	while (n--)
	{
		*pdst = *psrc;
		if (*psrc == (unsigned char)c)
			return ((void *)(pdst + 1));
		pdst++;
		psrc++;
	}
	return (NULL);
}
