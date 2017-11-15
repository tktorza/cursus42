/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strsub.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 11:28:35 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:25 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char	*ft_strsub(char const *s, unsigned int start, size_t len)
{
	char		*new;
	size_t		i;
	size_t		j;

	if (!s)
		return (NULL);
	i = 0;
	j = 0;
	new = (char *)malloc(sizeof(char) * len + 1);
	if (new == NULL || start > ft_strlen(s))
		return (NULL);
	while (i < start)
		i++;
	while (s[i] && j < len)
	{
		new[j] = ((char *)s)[i];
		i++;
		j++;
	}
	new[j] = '\0';
	return (new);
}
