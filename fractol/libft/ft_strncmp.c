/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strncmp.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 17:24:52 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/01 10:30:59 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

int		ft_strncmp(const char *s1, const char *s2, size_t n)
{
	size_t i;

	i = 0;
	while (i < n && s1[i] && s2[i])
	{
		if ((unsigned char)s1[i] == (unsigned char)s2[i])
			i++;
		else
			return
				((int)((unsigned char)(s1[i]) - (unsigned char)(s2[i])));
	}
	if (i == n)
		return (0);
	return ((int)(unsigned char)s1[i] - (unsigned char)s2[i]);
}
