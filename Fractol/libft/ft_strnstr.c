/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strnstr.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 14:53:58 by tktorza           #+#    #+#             */
/*   Updated: 2015/12/09 18:33:05 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "libft.h"

char	*ft_strnstr(const char *s1, const char *s2, size_t n)
{
	size_t i;
	size_t j;

	i = 0;
	if (!ft_strlen(s2))
		return ((char*)s1);
	while (i < n && s1[i])
	{
		j = 0;
		if (s2[j] == s1[i + j])
		{
			while ((s2[j] == s1[i + j]) && (i + j) < n)
			{
				if (s2[j + 1] == '\0')
					return ((char*)s1 + i);
				j++;
			}
		}
		i++;
	}
	return (NULL);
}
