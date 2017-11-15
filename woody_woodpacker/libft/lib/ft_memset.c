/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_memset.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/24 14:34:36 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:05:39 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

void	*ft_memset(void *b, int c, size_t len)
{
	unsigned char	*s;

	s = b;
	while (len--)
		*s++ = (unsigned char)c;
	return (b);
}
