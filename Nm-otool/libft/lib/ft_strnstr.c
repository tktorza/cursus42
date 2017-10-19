/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strnstr.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/28 16:10:47 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:19 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../inc/libft.h"

char	*ft_strnstr(const char *s1, const char *s2, size_t n)
{
	int	i;
	int	k;

	if (!*s2)
		return ((char *)s1);
	i = 0;
	k = 0;
	while (s1[i] && n > 0)
	{
		while (s2[k] && s1[i + k] == s2[k])
			k++;
		if (!s2[k] && ft_strlen(s2) <= n)
			return ((char *)s1 + i);
		k = 0;
		i++;
		n--;
	}
	return (NULL);
}
