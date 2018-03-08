/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_memmove.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/27 14:17:24 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/09 18:04:58 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

void	*ft_memmove(void *dst, const void *src, size_t len)
{
	char		*str;
	const char	*bis;

	str = (char *)dst;
	bis = (char *)src;
	if (bis <= str)
	{
		str += len - 1;
		bis += len - 1;
		while (len--)
		{
			*str-- = *bis--;
		}
	}
	else
	{
		ft_memcpy(dst, src, len);
	}
	return (dst);
}
