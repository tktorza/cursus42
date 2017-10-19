/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strncmp.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/24 20:32:20 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:12 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

int		ft_strncmp(const char *s1, const char *s2, size_t n)
{
	unsigned int i;

	i = 0;
	while (s1[i] && s2[i] && n != 0 && s1[i] == s2[i])
	{
		n--;
		i++;
	}
	if (s1[i] == s2[i] || (n == 0 && *--s1 == *--s2))
		return (0);
	else
		return ((unsigned char)s1[i] - (unsigned char)s2[i]);
}
