/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strstr.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/25 14:39:34 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:24 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

char	*ft_strstr(const char *s1, const char *s2)
{
	int	i;
	int	k;

	if (!*s2)
		return ((char *)s1);
	i = 0;
	k = 0;
	while (s1[i])
	{
		while (s2[k] && s1[i + k] == s2[k])
			k++;
		if (!s2[k])
			return ((char *)s1 + i);
		k = 0;
		i++;
	}
	return (NULL);
}
